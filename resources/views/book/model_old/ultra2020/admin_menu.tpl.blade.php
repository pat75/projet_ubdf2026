{{-- Porte depuis 2011_html_pages_v2/ultra2020/admin_menu.tpl.php (_outils/porter_gabarits.py) --}}

<?php // $b->us_formule=0; ?>

<a class="item menu_header">

	<?php if ($b->inc_action_view == 'df') { ?>
        <img class="ubdf_logo" src="<?=$b->url_abs_site;?>/img_front_df/dustfolio.svg" style="width:75%;margin:26px auto;">
    <?php } else { ?>
        <img class="ubdf_logo" src="<?=$b->url_abs_site;?>/img_front/ub_logo_mobile.svg">
        <span><?=$b->modele_book_titre;?></span>
	<?php } ?>


</a>



<a class="item menu_space+" href="<?=$b->url_abs_site;?>/ubaction__user_pref_form">
    <span data-tooltip="<?=__('Retour gestion du compte')?>"  data-position="top left">
        <i class="arrow left icon" ></i>
    </span>

   <div id="menu_loader">

        <span class="loading_ hidden">
            <div class="ui small active inline loader"></div>
            <span><?=__('Sauvegarde')?></span>
        </span>

        <span class="doing_ hidden">
            <i class="check circle outline+ icon "></i>
            <span><?=__('Enregistré')?></span>
        </span>

       <span class="error_ hidden">
            <i class="exclamation icon "></i>
            <span><?=__('Erreur')?></span>
        </span>

   </div>
</a>



<a class="item btn_modal_portfolio">
    <i class="grid layout icon"></i><?=__('Contenu')?> <strong><?=__('Images')?></strong>
</a>
<a class="item btn_modal_page menu_space+">
    <i class="folder outline icon"></i><?=__('Contenu')?> <strong><?=__('Pages')?></strong>
</a>



<a class="item menu_space_top">
	<?php if ($b->us_formule==1) {;?>
        <div class="ui  form">
            <div class="inline field">
                <label><i class="unlock icon"></i><strong><?=__('Thème graphique')?></strong></label>
                <br/><br/>
                <div class="tiny three ui buttons" id="btn_mode_theme">
                    <button class=" ui button" data-value="theme_black"><?=__('Sombre')?></button>
                    <button class=" ui button" data-value="theme_gris"><?=__('Moyen')?></button>
                    <button class=" ui button active" data-value="theme_white"><?=__('Claire')?></button>
                </div>
            </div>
        </div>
	<?php } else { ?>
        <div style="padding-top: 5px;margin-top:-5px;" data-tooltip="Réservé aux Comptes Premium">
            <div class="ui disabled form">
                <div class="inline disabled field">
                    <label><i class="lock icon"></i><strong><?=__('Thème graphique')?></strong></label>
                    <br/><br/>
                    <div class="tiny three ui buttons" id="btn_mode_theme">
                        <button class=" ui button" data-value="theme_black"><?=__('Sombre')?></button>
                        <button class=" ui button" data-value="theme_gris"><?=__('Moyen')?></button>
                        <button class=" ui button active" data-value="theme_white"><?=__('Claire')?></button>
                    </div>
                </div>
            </div>
        </div>
	<?php } ?>
</a>






<!--
<a class="item admin_mode_expert">
    <i class="hashtag icon "></i><?=__('Mots clés')?></strong>
</a>
-->



<!-- visuel size -->
<a class="item admin_mode_expert+ btn_visuel_size">
	<?php if ( $b->us_formule==1 ) { ?>

        <div class="ui form">
            <div class="inline field">
                <label><i class="th large icon "></i><?=__('Espacement visuels')?></label>
                <br/><br/>
                <div class="tiny three ui buttons" id="btn_mode_visuel_size">
                    <button class=" ui button" data-value="small"><?=__('Petit')?></button>
                    <button class=" ui button" data-value="normal"><?=__('Moyen')?></button>
                    <button class=" ui button" data-value="large"><?=__('Large')?></button>
                </div>
            </div>
        </div>

	<?php } else { ?>

        <div style="padding-top: 5px;margin-top:-5px;" data-tooltip="<?=__('Réservé aux Comptes Premium')?>">
            <div class="ui disabled form">
                <div class="inline disabled field">
                    <label style="width:100%"><i class="lock icon"></i><?=__('Espacement visuels')?>
                        <i class="th large icon " style="float:right"></i></label>
                    <br/><br/>
                    <div class="tiny three ui buttons" id="btn_mode_theme">
                        <button class=" ui button" data-value="small"><?=__('Petit')?></button>
                        <button class=" ui button" data-value="normal"><?=__('Moyen')?></button>
                        <button class=" ui button" data-value="large"><?=__('Large')?></button>
                    </div>
                </div>
            </div>
        </div>

	<?php } ?>
</a>

<!-- sociale -->
<a class="item admin_mode_expert btn_popup_social">
    <i class="share alternate icon "></i><?=__('Lien réseaux sociaux')?></strong>
</a>


<!-- css -->
<a class="item admin_mode_expert btn_popup_css">
	<?php if ($b->us_formule==1) {;?>

        <i class=" code icon"></i>
        <?=__('CSS expert')?>

	<?php } else { ?>
    <div style="padding-top: 5px;margin-top:-5px;" data-tooltip="<?=__('Réservé aux Comptes Premium')?>">
        <div class="ui  form">
            <div class="inline disabled field" >
                <label>
                <i class="lock icon"></i>
                <?=__('CSS expert')?>
                </label>
                <i class=" code icon" style="float:right"></i>
            </div>
        </div>
    </div>
	<?php } ?>
</a>









<a class="item">
	<?php if ($b->us_formule==1 ) {;?>
        <div class="ui  form">
            <div class="inline field">
                <div class="ui toggle checkbox checked">
                    <input type="checkbox" tabindex="0" class="hidden" id="btn_mode_cursor" checked>
                    <label class="popup_tooltip" data-html="<?=__('Visible en mode public')?>"><strong><?=__('Curseur animé')?></strong></label>
                </div>
            </div>
        </div>
	<?php } else { ?>
        <div class="ui disabled form">
            <div class="inline disabled field">
                <div class="ui toggle checkbox checked">
                    <input type="checkbox" tabindex="0" class="hidden" id="btn_mode_cursor" checked>
                    <label><i class="lock icon"></i><strong><?=__('Curseur animé')?></strong></label>
                </div>
            </div>
        </div>
	<?php } ?>
</a>





<a class="item">
    <div class="ui  form">
        <div class="inline field">
            <div class="ui toggle checkbox">
                <input type="checkbox" tabindex="0" class="hidden" id="btn_mode_expert" <?=($b->obj_pref->data->expert)?'checked':''?> >
                <label><strong><?=__('Mode expert')?></strong></label>
            </div>
        </div>
    </div>
</a>







<a class="item menu_space_top" >
	<?php if ($b->us_formule==1 ) {;?>

        <div class="ui form send_ask_selection" >

            <button class="ui left labeled icon fluid small ask_action button popup_tooltip"
                    data-html="<?=__('Demande pour promovoir<br/>votre book en page d’accueil')?>"
                    id="us_formule_ask_date"
                    data-pk="us_formule_ask_date" data-type="submit" data-val="<?=$b->us_formule_ask_date?>" >
                <i class="gem outline icon"></i>
				<?=__('Demande de sélection')?>
            </button>

            <span class="label_right">
            <button class="ui left labeled icon fluid olive small button ask_send popup_tooltip hidden"
                    data-html="<?=__('Votre demande vient d’être envoyé<br/>Si vous êtes sélectionné vous receverez un mail d’ici quelques jours')?>">
                <i class="clock outline icon"></i>
              <?=__("Demande envoyée.")?>
            </button>
            <button class="ui left labeled icon fluid yellow small button ask_last popup_tooltip hidden"
                    data-html="<?=__('Vous pouvez effectuer une nouvelle demande<br/>d’ici quelques jours<br/>Pensez à mettre à jour votre book')?>">
                <i class="hourglass half icon"></i>
                <?=__("Re-sélection dans ")?>
              <span class="nb_day"></span> <?=__("jours")?>
            </button>
        </span>

        </div>


	<?php } else { ?>
        <div style="padding-top: 5px;margin-top:-5px;" data-tooltip="<?=__('Réservé aux Comptes Premium')?>">

            <div class="ui  form">
                <div class="inline disabled field" >
                    <label style="float: left; display: grid;margin-top: 6px;"><i class="lock icon"></i></label>
                    <div class="tiny one ui buttons" >
                        <button class="ui button "  >
                            <i class="arrow circle up icon"></i>
							<?=__('Demande de sélection')?>
                        </button>

                    </div>
                </div>
            </div>

        </div>

	<?php } ?>
</a>


<?php
    // marketing promo
    //


if ( request()->cookie("us_nb_login") % 6 == 0 ) {

    ultra2020__admin_promo();


    $us_promo = null;
    $json     = json_decode(null);

    if ($us_promo == 6) {
        $json->visuel     = $json->visuel_6;
        $json->visuel_alt = $json->visuel_alt_6;
        $json->type       = $json->type_6;
    } else {
        $json->visuel     = $json->visuel_12;
        $json->visuel_alt = $json->visuel_alt_12;
        $json->type       = $json->type_12;
    }
    ?>

    <?php if ( $b->us_formule != 1 || false ) { ?>
        <div class="closebox promo_formule">
            <div class="close_btn">
                <span class="close_btn_close 	icon fonticon-x-circle"></span>
                <span class="close_btn_open 	icon fonticon-plus-circle"></span>
            </div>
            <a  href="<?=$b->url_abs_site;?>/formules" class="promotion_nav_ fm-<?=$json->type;?>   close_container">
                <img src="<?=$b->url_abs_site;?>/<?=$json->visuel?>" alt="<?=$json->visuel_alt?>">
            </a>
        </div>
    <?php } ?>

<?php } ?>



