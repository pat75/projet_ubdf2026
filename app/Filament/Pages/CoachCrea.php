<?php

namespace App\Filament\Pages;

use App\Mail\CoachingMail;
use App\Models\CoachMessage;
use App\Models\CreatifActivity;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Coach crea : les dernieres operations des createurs sur leur book, et
 * les messages de coaching prepares par l'IA (ubdf:preparer-coaching),
 * a relire, corriger puis envoyer.
 */
class CoachCrea extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Coach créa';

    protected static ?string $title = 'Coach créa';

    protected static string|\UnitEnum|null $navigationGroup = 'Créatifs';

    protected string $view = 'filament.pages.coach-crea';

    /** Jours d'activite affiches. */
    public const JOURS = 7;

    /** @var array<int, array{objet: string, corps: string}> brouillons en cours d'edition */
    public array $brouillons = [];

    public function mount(): void
    {
        $this->brouillons = CoachMessage::where('statut', 'brouillon')->get()
            ->mapWithKeys(fn ($m) => [$m->id => ['objet' => $m->objet, 'corps' => $m->corps]])->all();
    }

    public function envoyer(int $id): void
    {
        $message = CoachMessage::where('statut', 'brouillon')->findOrFail($id);
        $this->validate([
            "brouillons.$id.objet" => 'required|string|max:255',
            "brouillons.$id.corps" => 'required|string|max:5000',
        ]);

        // L'interrupteur a pu etre coupe entre la preparation et l'envoi.
        if ($message->user->bookSetting?->coaching === false) {
            $this->clore($message, 'ignore');
            Notification::make()->title('Le créatif a désactivé les conseils : message écarté.')->warning()->send();

            return;
        }

        $message->fill($this->brouillons[$id]);
        $this->clore($message, 'envoye');
        Mail::to($message->user->email)->send(new CoachingMail($message));

        Notification::make()->title('Message envoyé à '.$message->user->fullName())->success()->send();
    }

    public function ignorer(int $id): void
    {
        $this->clore(CoachMessage::where('statut', 'brouillon')->findOrFail($id), 'ignore');
    }

    private function clore(CoachMessage $message, string $statut): void
    {
        $message->update([
            'statut' => $statut,
            'traite_par' => Auth::guard('admin')->user()?->name,
            'envoye_le' => $statut === 'envoye' ? now() : null,
        ]);
        unset($this->brouillons[$message->id]);
    }

    protected function getViewData(): array
    {
        $messages = CoachMessage::with('user.bookSetting')->where('statut', 'brouillon')->latest()->get();

        // Dernier message envoye a chacun : « il y a N jours ».
        $derniersEnvois = CoachMessage::where('statut', 'envoye')->whereIn('user_id', $messages->pluck('user_id'))
            ->selectRaw('user_id, max(envoye_le) as dernier')->groupBy('user_id')->pluck('dernier', 'user_id');

        // ponytail: 7 jours charges d'un coup ; paginer par createur si le journal grossit trop.
        $activite = CreatifActivity::with('user.bookSetting')
            ->where('created_at', '>', now()->subDays(self::JOURS))
            ->latest('created_at')->latest('id')->get()
            ->groupBy('user_id');

        return compact('messages', 'derniersEnvois', 'activite');
    }
}
