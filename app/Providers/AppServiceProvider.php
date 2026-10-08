<?php

namespace App\Providers;

use App\Models\AccueilBloc;
use App\Models\Actualite;
use App\Models\Admin;
use App\Models\AdminActivity;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\Conversation;
use App\Models\Invoice;
use App\Models\MarketingOffer;
use App\Models\NewsletterMail;
use App\Models\PromoCode;
use App\Models\Reglage;
use App\Models\Selection;
use App\Models\User;
use App\Models\Visitor;
use App\Observers\JournalAdmin;
use App\Observers\JournalCreatif;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         | Intervention Image, pilote choisi a l'execution.
         |
         | Imagick rend mieux les degrades et gere les profils de couleur ;
         | GD est toujours la. Le MAMP de developpement n'a pas Imagick, le
         | serveur de production peut l'avoir : le choix se fait donc sur ce
         | qui est charge, sans configuration a tenir a jour.
         */
        $this->app->singleton(ImageManager::class, fn () => new ImageManager(
            extension_loaded('imagick') ? new ImagickDriver : new GdDriver
        ));
    }

    public function boot(): void
    {
        // Site de demonstration : aucun courriel n'atteint un vrai compte.
        if ($adresse = config('mail.toujours_vers')) {
            Mail::alwaysTo($adresse);
        }

        /*
         | Un creatif et un visiteur ne partagent jamais une session : se
         | connecter sous l'un ferme l'autre, quel que soit le chemin
         | (formulaire, Google, inscription, reinitialisation...).
         */
        Event::listen(function (Login $connexion) {
            $autre = ['web' => 'visitor', 'visitor' => 'web'][$connexion->guard] ?? null;

            if ($autre && Auth::guard($autre)->check()) {
                Auth::guard($autre)->logout();
            }

            // Derniere connexion du createur, hors prise d'identite par un
            // administrateur. saveQuietly : ni journal, ni updated_at.
            if ($connexion->guard === 'web' && ! session()->has(\App\Http\Controllers\Admin\PriseIdentiteController::SESSION)) {
                $connexion->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });

        /*
         | Journal du back-office : les modeles que les administrateurs
         | modifient. L'observateur ne retient que les ecritures faites par
         | un administrateur connecte.
         */
        foreach ([
            User::class, Visitor::class, PromoCode::class, Selection::class, Campaign::class, Category::class, Admin::class,
            NewsletterMail::class, Actualite::class, Invoice::class, Conversation::class, Reglage::class,
            CmsPage::class, CmsPost::class, AccueilBloc::class, MarketingOffer::class,
        ] as $modele) {
            $modele::observe(JournalAdmin::class);
        }

        // Coach crea : les modifications du book faites par le createur lui-meme.
        foreach ([
            \App\Models\Gallery::class, \App\Models\Media::class, \App\Models\BookSetting::class,
            \App\Models\BookSection::class, \App\Models\BookArticle::class, \App\Models\PageImage::class,
        ] as $modele) {
            $modele::observe(JournalCreatif::class);
        }

        /*
         | Bouton « Filtres » de toutes les listes de l'admin : un vrai
         | bouton libelle, au meme rendu que « Exporter en CSV », plutot
         | qu'une petite icone qu'on ne voit pas. Le badge du nombre de
         | filtres actifs reste ajoute par Filament.
         */
        Table::configureUsing(fn (Table $table) => $table->filtersTriggerAction(
            fn (Action $action) => $action->button()->label('Filtres')->icon('heroicon-o-funnel')->extraAttributes(['class' => 'ub-bouton-filtres'])
        )->filtersApplyAction(
            // Le panneau se referme des qu'on applique : `close` est la methode
            // Alpine du dropdown (filamentDropdown), dont le bouton est un enfant.
            // x-init plutot que x-on:click / alpineClickHandler : ceux-ci
            // remplacent le wire:click qui applique reellement les filtres.
            fn (Action $action) => $action->extraAttributes(['x-init' => "\$el.addEventListener('click', () => close())"])
        ));

        // Connexions et deconnexions du back-office.
        foreach ([Login::class => 'login', Logout::class => 'logout'] as $evenement => $action) {
            Event::listen($evenement, function ($e) use ($action) {
                if ($e->guard !== 'admin' || ! $e->user) {
                    return;
                }

                AdminActivity::create([
                    'admin_id' => $e->user->getKey(),
                    'admin_name' => $e->user->name,
                    'action' => $action,
                    'subject_type' => Admin::class,
                    'subject_id' => (string) $e->user->getKey(),
                    'subject_label' => $e->user->name,
                    'ip' => request()->ip(),
                ]);
            });
        }
    }
}
