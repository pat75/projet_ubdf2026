<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `us_affhome` n'est pas un indicateur de publication.
 *
 * La colonne avait ete reprise sous le nom `is_published`, et servait a
 * decider si un book etait visible. C'est un contresens : dans le legacy,
 * la visibilite tient aux deux indicateurs de **diffusion**, et
 * `us_affhome` ne designe que la selection editoriale mise en avant.
 *
 *     $where .= " AND user.us_delete = 'false' ";
 *     $where .= " AND user_pref.us_pf_diff_web = 'true'
 *                  AND user_pref.us_pf_diff_ub = 'true' ";   // diffusion
 *     if ($flt_sel == "true") $where .= "AND user.us_affhome = true ";  // selection
 *     $order = 'user.us_affhome ASC, user.us_img_nb DESC';
 *
 * Consequence du contresens : les 8 comptes Dustfolio de l'echantillon de
 * developpement, tous diffuses mais aucun en selection, etaient invisibles.
 * Le portail Dustfolio n'affichait donc aucun book.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('is_published', 'in_home_selection');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('in_home_selection', 'is_published');
        });
    }
};
