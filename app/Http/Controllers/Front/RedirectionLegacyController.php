<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CmsPost;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Redirections des anciennes URL qui demandent de regarder en base. */
class RedirectionLegacyController extends Controller
{
    /** `facture_n__<id legacy>` : la facture si elle existe, sinon la liste. */
    public function facture(int $id): RedirectResponse
    {
        $facture = Invoice::where('legacy_id', $id)->first();

        return redirect($facture ? '/espace/factures/'.$facture->id : '/espace/formule', 301);
    }

    /** `book_<login>`, `minibook_<login>`, `-<login>` : le book du createur. */
    public function book(string $login): RedirectResponse
    {
        $creatif = User::where('login', $login)->first();

        return redirect($creatif ? $creatif->bookUrl() : '/', 301);
    }

    /** `<slug>__wpactu_<id>` : l'article du magazine, repris dans `cms_posts`. */
    public function actualite(string $slug, int $id): RedirectResponse
    {
        $article = CmsPost::where('legacy_id', $id)->first();

        return redirect($article ? '/actus/'.$article->slug : '/actus', 301);
    }

    /** `/support` et `/contact` : le site vitrine de la marque servie. */
    public function support(Request $requete): RedirectResponse
    {
        $marque = $requete->attributes->get('marque');

        return redirect($marque?->code === 'df'
            ? 'https://www.dustfolio.fr/contact/'
            : 'https://www.ultrabook.pro/contact/', 301);
    }

    /**
     * Ancien lien de fil de discussion. Le jeton du legacy n'etait pas
     * verifiable : ces liens ne donnent plus acces au fil.
     */
    public function filPerime(): RedirectResponse
    {
        return redirect('/')
            ->with('statut', __('Ce lien de conversation n’est plus valable. Les créatifs retrouvent leurs demandes dans leur espace ; pour écrire à nouveau, passez par le formulaire de contact du book.'));
    }
}
