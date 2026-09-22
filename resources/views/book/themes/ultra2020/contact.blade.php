{{-- Porte depuis 2011_html_pages_v2/ultra2020/contact.tlp.php (_outils/porter_gabarits.py) --}}

<!--
<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=fr"></script>
-->




<article class="twelve wide column column-contact column_ultrafrais">


<h1 class="<?=(($b->us_formule == 1)?'admin_edit admin_mode_textarea':'admin_edit_premium')?>"
    data-admin-edit="edit-trash"
    data-admin-objet="contact_titre"
    data-admin-edit-viaobj="true">
	<?=ultra2020__stripslashes_($b->obj_pref->data->contact_titre);?>
</h1>

    <!--  intermediate form  response error -->
    <div class="ui segment basic space1 hidden" id="segment_intermediate_reponse_error">
		<?=( 'Nous avons rencontré une ou plusieurs erreurs dans le formulaire' )?>
        <p class="error_list"></p>

        <div class="btn_back_form"><i class="angle left icon "></i><?=( 'Retour' )?></div>
    </div>

    <!--  intermediate form  response-->
    <div class="ui segment basic space1 hidden bubble validate" id="segment_intermediate_reponse">

        <div class="ui header">
            <i class="check icon"></i>
            <?=( 'Merci' )?>
        </div>
		<?=( 'Votre demande vient d’être envoyée' )?>
    </div>

    <!--  intermediate form -->
    <div class="ui segment basic space1 " id="segment_intermediate_form">
    <form class="ui form" id="intermediate_form">

        <?php  /*
<!--
action: work_A_contact
mf_request_detail:
us_dir: t91
us_key:
g-recaptcha-response: 03AOLTBLRMOrRcgMX99o08yCuPslYTc7BSQjlJ_42regePwok8cs5luYxz9nsUUE-jiJPJd7ES9JOY6HO6vdK_dAx-gBZ7r8hzMibv3ciCmBk2GipfQgEpxxc3uHU8T1wBfKrcHsdEJJphEGNpnfJog8sT9iPnhONtpFg0g8DFUuI-1iqgKU4jHoEXgV0XwICQK22MMWYJAI8yEjIARnrwXaey44IK5TrYJD6FfRYDieTPPeoUO9U_V5G-q2EIrGlOnEe1mr622oghsGmTE-o05mbYltyDPB5UzG5eOBW1OhofjTo22046MGsQNb9Ep-pOCu4Oftho-t-yhHB8y5E1qWHBSmimL9fR6xDLgREJGCasHuvyrv2v-szkQj0uQ-u1huNmk6p3RUOgcpTr6QbUbohpkppUA9CkMwtzc84yPsLXoJuAa0DAvb-_fiSsZDLrNNmghpRbwRQ7MfVo8pyBCPr20bZlgOc4LAN_AZvZm32sIucxNku1DjnDVE16333VsnNor71YSJQ6
us_book_visuel: /users_2/t/9/t91/img_/photo_b13_jpeg__1657339.jpeg
us_message: gdsfg
us_nom_prenom: sdfhdfg
us_mail: patrice2016@@polygun.fr
us_societe:
us_tel:
-->
        <!--
        <input type="hidden" name="form"        value="ajax">
        <input type="hidden" name="fm_user"     value="t91">
        <input type="hidden" name="fm_key"      value="53989f7a01c047789c4ad0f48887b154">


action: work_A_contact
us_dir: t91
g-recaptcha-response: 03AOLTBLT0bVQWEt9fYKL7QlRmrWVn9tep9VXDnxIPSyZPUZaYd2zCkGgpf0ZIThOns1HxCURTxbLsUiqa5gVr_PknLgq5ZoRnIcI4UEDk_-NhQFSinWqmRDtxVpgks3ydKDWQPhXjlKdWzLxqSN9A636u7qoYNFzknLKrqjrsKfpfc0JFJ_8iIySbM9yzcpquNSqYuBp_OP8D54lZbHlwNOp7dLgoDnG4CHRD3Kzc2MwS7lcfSYpssIb0cRVwgvA1e4nPcoCrYY_L40RCuUr3t5WMpVj3AEpaMCKbbPIRDgAdU0cvZE2F9AyTg0pgssNsq9Qtb3DjyyGovLYkJsduIUmakOitQMi7wGuV5AGeWGPh-SXJIPZOFCHfD-_7MHZvxrzsVr3tEdBPQVP70rTPvMEWmxD0pWViuLGLFN3ZtxA1-geRl1m6wl8xmyV2dh3kFWmxwpEWA7mYpUQbDBKJjr76WMYanJCfYm_k-PNO5ACji9u4o54D2r0QmXUsa15GDYcO8fCI4xOP
us_book_visuel:
us_message: Test https://t91.ultra-book.com/contact
us_nom_prenom: Test Pat
us_mail: patrice@@polygun.net



<input type="hidden" name="us_key"          value=""> f59f4118fa0e4f86f4a4dfdf67527f5031d004d7

-->
*/?>

        <input type="hidden" name="g-recaptcha-response" value="">
        <input type="hidden" name="miel-recaptcha"  value="">

        <input type="hidden" name="url"             value="<?=$b->url_abs_site;?>/intermediate_send_frombook">
        <input type="hidden" name="action"          value="work_A_contact">
        <input type="hidden" name="us_dir"          value="<?=$b->us_dir;?>">
        <input type="hidden" name="us_book_visuel"  value="">
        <input type="hidden" name="us_key"          value="">

	    <?php

	    $conf['form_antibot_secret'] = 'changez-cette-valeur-par-un-secret-ldkljfhgldkjfhgkj5465qsd+';


	    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
	    $ua = request()->userAgent() ?? '';
	    $ts = time();
	    $secret = $conf['form_antibot_secret'] ?? '';
	    $sig = ($secret !== '') ? hash_hmac('sha256', $ip.'|'.$ua.'|'.$ts, $secret) : '';
	    ?>
        <input type="text" name="mf_hp" value="" style="display:none" autocomplete="off" tabindex="-1" />
        <input type="hidden" name="mf_ts" value="<?php echo (int)$ts; ?>" />
        <input type="hidden" name="mf_sig" value="<?php echo htmlspecialchars($sig, ENT_QUOTES); ?>" />



        <div class="field">
            <label><?=( 'Message')?></label>
            <textarea name="us_message" rows="2" placeholder="<?=( 'Mon message')?>"></textarea>
        </div>
        <div class="field">
            <label><?=( 'Prénom, nom')?></label>
            <input name="us_nom_prenom" type="text" name="name" placeholder="<?=( 'Mon nom et prénom')?>" value="">
        </div>
        <div class="field">
            <label><?=( 'Mail')?></label>
            <input name="us_mail" type="text" name="mail" placeholder="<?=( 'Mon e-mail')?>"  value="">
        </div>

        <div class="field field_info+ cursor_classique">
            <label class="info "
                   data-title="<?=( 'Protection des données et suivi du message')?>"
                   data-content="<?=( 'Les informations indiquées dans ce formulaire ne seront pas diffusées à des tiers,
                   autres que le destinataire du message et la plateforme Ultra-book.
                   Un mail vous permettra de suivre l’évolution de votre message et surtout de vérifier s’il a été lu par le destinataire.')?>"

            >
                <img src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/svg/information.svg" alt="Information" class="icon_svg">
                <?=( 'Protection des données et suivi du message')?>
            </label>
        </div>

        <?php /*
        <!--
        <div class="field field_info">
            <label class="option ">
                <img src="svg/plus.svg" alt="Information" class="icon_svg">
                Informations complémentaires, optionnelles
            </label>
        </div>
        -->

        <!--
        <div class="field field_info">
        <div class="g-recaptcha" data-sitekey="6Lc2eRQTAAAAAL1to7OJL12n4xLQE9i8O-i9G4Pn" ></div>
        </div>
        -->
        */?>

        <button class="ui button valider_submit_inter cursor_effect" type="submit"><?=( 'Envoyer')?></button>
    </form>



        <div class="contact_footer <?=(($b->us_formule == 1 )?'admin_edit admin_mode_textarea':'admin_edit_premium')?>"
            data-admin-edit="edit-trash"
            data-admin-objet="contact_footer"
            data-admin-edit-viaobj="true">
		    <?=ultra2020__stripslashes_($b->obj_pref->data->contact_footer);?>
        </div>


    </div>

</article>
