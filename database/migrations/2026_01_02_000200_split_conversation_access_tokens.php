<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Acces a un fil de discussion : un jeton par partie, stocke hache.
 *
 * Le systeme de 2019 posait un probleme de fond. Les deux liens envoyes par
 * courriel — celui du visiteur et celui du creatif — portaient le **meme**
 * jeton, et le segment de controle etait derive de ce jeton :
 *
 *     verif_from_cust($token) = substr(substr($token,0,4).substr($token,2,4), 0, 6)
 *     verif_from_user($token) = substr(substr($token,1,4).substr($token,3,4), 0, 6)
 *
 * Il ne verifie donc rien : quiconque detient son propre lien peut calculer
 * celui de l'autre partie, lire le fil de son point de vue et y repondre en
 * son nom. Le controle SQL ne portait d'ailleurs que sur token + selector,
 * le role venant du seul parametre `from` de l'URL.
 *
 * Deux jetons independants les remplacent, tires au hasard et stockes
 * haches : la base volee ne rend aucun lien utilisable. Le `selector`, lui,
 * reste en clair — c'est lui qui designe la ligne, sans quoi il faudrait
 * comparer le hachage de toutes les conversations.
 *
 * Les jetons de ub2020 ne sont pas repris : ils ont ete emis sous ce schema
 * et resteraient exploitables. Les anciens liens cessent de fonctionner ;
 * la valeur d'origine est conservee pour tracer les conversations migrees.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->renameColumn('token', 'legacy_token');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->string('owner_token', 64)->nullable()->after('selector');
            $table->string('sender_token', 64)->nullable()->after('owner_token');

            // Le legacy prefixait le corps du message d'une banniere rouge
            // pour signaler un spam : un marqueur de traitement ecrit dans
            // la donnee elle-meme, impossible a retirer ensuite.
            $table->boolean('is_spam')->default(false)->index()->after('book_image');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->string('selector', 32)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['owner_token', 'sender_token', 'is_spam']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->renameColumn('legacy_token', 'token');
        });
    }
};
