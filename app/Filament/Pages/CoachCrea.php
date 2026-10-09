<?php

namespace App\Filament\Pages;

use App\Console\Commands\PreparerCoachingCommand;
use App\Mail\CoachingMail;
use App\Models\CoachMessage;
use App\Models\CreatifActivity;
use App\Models\Reglage;
use App\Models\User;
use App\Services\Coach\Diagnostic;
use App\Services\Coach\Redacteur;
use BackedEnum;
use Filament\Notifications\Notification;
use App\Filament\Resources\Users\Tables\UsersTable;
use Filament\Pages\Page;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Coach crea : les dernieres operations des createurs sur leur book, et
 * les messages de coaching prepares par l'IA (ubdf:preparer-coaching),
 * a relire, corriger puis envoyer.
 */
class CoachCrea extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Coach créatif';

    protected static ?string $title = 'Coach créatif';

    protected static string|\UnitEnum|null $navigationGroup = 'Créatifs';

    protected string $view = 'filament.pages.coach-crea';

    /** Jours d'activite affiches. */
    public const JOURS = 7;

    /** @var array<int, array{objet: string, corps: string}> brouillons en cours d'edition */
    public array $brouillons = [];

    /** IA de redaction (Redacteur::CHOIX), enregistree des qu'elle change. */
    public string $coachIa = 'nvidia';

    /** Titre suivi d'un menu rapide vers les deux blocs de la page. */
    public function getHeading(): Htmlable
    {
        $lien = fn (string $ancre, string $libelle) => '<a href="#'.$ancre.'" class="ub-coach-ancre">'.$libelle.'</a>';

        return new HtmlString('<span class="ub-coach-titre">Coach créatif<span class="ub-coach-menu">'
            .$lien('operations', 'Dernières opérations').$lien('messages', 'Messages à envoyer').'</span></span>');
    }

    public function mount(): void
    {
        $this->coachIa = Redacteur::choix();
        $this->brouillons = CoachMessage::where('statut', 'brouillon')->get()
            ->mapWithKeys(fn ($m) => [$m->id => ['objet' => $m->objet, 'corps' => $m->corps]])->all();
    }

    /**
     * Les books actifs sur la periode, presentes par la table de
     * /admin_/users (memes colonnes, filtres et actions), plus une
     * colonne qui deplie leurs operations.
     */
    public function table(Table $table): Table
    {
        $recentes = fn ($query) => $query->where('created_at', '>', now()->subDays(self::JOURS));

        $table = UsersTable::configure($table);

        return $table
            ->query(fn (): Builder => User::query()
                ->whereHas('creatifActivities', $recentes)
                ->withMax(['creatifActivities as derniere_operation' => $recentes], 'created_at')
                ->with(['creatifActivities' => fn ($query) => $recentes($query)->latest('created_at')->latest('id')]))
            // Fleche de depliage et bouton de generation en tete de ligne, avant l'avatar.
            ->columns([
                ViewColumn::make('derniere_operation')->label('Opérations')->sortable()
                    ->view('filament.pages.coach-crea-operations'),
                // Sans la colonne « État » de /admin_/users.
                ...array_values(array_diff_key($table->getColumns(), ['blocked_at' => true])),
            ])
            // Pas de cases a cocher : les actions groupees servent sur /admin_/users.
            ->selectable(false)
            ->defaultSort('derniere_operation', 'desc')
            ->heading('Dernières opérations ('.self::JOURS.' jours)');
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

    public function updatedCoachIa(string $valeur): void
    {
        abort_unless(isset(Redacteur::CHOIX[$valeur]), 422);
        Reglage::definirTexte(Reglage::COACH_IA, $valeur);
        Notification::make()->title('IA du coach : '.Redacteur::CHOIX[$valeur])->success()->send();
    }

    /** Brouillon a la demande, sans attendre les 24 h de la commande planifiee. */
    public function generer(int $userId, Diagnostic $diagnostic, Redacteur $redacteur): void
    {
        $creatif = User::with('bookSetting')->findOrFail($userId);

        if ($creatif->bookSetting?->coaching === false) {
            Notification::make()->title('Le créatif a désactivé les conseils.')->warning()->send();

            return;
        }

        $session = PreparerCoachingCommand::derniereSession($creatif);
        $conseils = $diagnostic->pour($creatif);

        try {
            $message = $redacteur->rediger($creatif, $session, $conseils);
        } catch (Throwable $e) {
            Notification::make()->title('Rédaction impossible : '.$e->getMessage())->danger()->send();

            return;
        }

        $m = CoachMessage::create(['user_id' => $creatif->id, 'session_fin' => $session->last()?->created_at ?? now(), 'diagnostic' => $conseils, ...$message]);
        $this->brouillons[$m->id] = ['objet' => $m->objet, 'corps' => $m->corps];
        Notification::make()->title('Brouillon prêt pour '.$creatif->fullName())->success()->send();
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


        return compact('messages', 'derniersEnvois');
    }
}
