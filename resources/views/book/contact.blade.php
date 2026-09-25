{{--
    Formulaire de contact du book.

    Balisage repris a l'identique du formulaire de production (genere par
    PFBC dans inc_user_book_modele.php::mod_contact) : memes champs, memes
    identifiants `ajax-element-N`, meme rappel `contactForm_callback`. Les
    feuilles des onze themes le ciblent par ces identifiants.

    Deux ecarts :
    - l'action vise le book lui-meme (POST /contact) et non plus
      `www.ultra-book.com/contact_reponse_frombook__<session>__<login>`,
      qui exposait l'identifiant de session PHP dans l'URL ;
    - `fm_key` (md5 de l'identifiant de session, reverse cote serveur) est
      remplace par le jeton CSRF de Laravel.
--}}
<style type="text/css">label span.required { color: #B94A48; }span.help-inline, span.help-block { color: #888; font-size: .9em; font-style: italic; }</style><form action="{{ route('book.contact.envoyer', ['login' => $b->us_dir]) }}" id="ajax" method="post"><label for="ajax-element-0"></label><input type="hidden" name="form" value="ajax" id="ajax-element-0"/><label for="ajax-element-1"></label><input type="hidden" name="fm_user" value="{{ $b->us_dir }}" id="ajax-element-1"/><label for="ajax-element-2"></label><input type="hidden" name="_token" value="{{ csrf_token() }}" id="ajax-element-2"/><label for="ajax-element-3"></label><input type="text" name="fm_contact_nom_prenom" id="ajax-element-3" placeholder="{{ __('Prénom, nom') }}"/><label for="ajax-element-4"></label><input type="email" name="fm_contact_mail" required id="ajax-element-4" placeholder="{{ __('Mail') }}"/><label for="ajax-element-5"></label><textarea rows="5" name="fm_contact_message" required id="ajax-element-5" placeholder="{{ __('Message') }}"></textarea><label for="ajax-element-6"></label>
		{{-- Captcha local (App\Services\Captcha\Captcha), a la place du reCAPTCHA Google. --}}
		<div class="ubdf-captcha" style="margin:8px 0 12px;display:flex;align-items:center;flex-wrap:nowrap">
			<img id="ajax-captcha-img" src="{{ route('book.captcha', ['login' => $b->us_dir, 'formulaire' => 'contact_book']) }}"
				 width="158" height="53" alt="{{ __('Code à recopier') }}" style="flex-shrink:0;background:#fff">
			<a href="#" onclick="document.getElementById('ajax-captcha-img').src='{{ route('book.captcha', ['login' => $b->us_dir, 'formulaire' => 'contact_book']) }}?'+Date.now();return false;"
			   title="{{ __('Autre code') }}" style="margin:0 8px;text-decoration:none">&#8635;</a>
			<input type="text" name="captcha" maxlength="4" required autocomplete="off" id="ajax-element-captcha"
				   placeholder="{{ __('Recopiez le code') }}" style="width:150px;margin:0"/>
		</div>

		<div class="form-actions"><input type="submit" value="{{ __('Envoyer') }} " name class="btn btn-primary" id="ajax-element-7"/></div></form><script type="text/javascript">jQuery(document).ready(function() {		jQuery("#ajax").bind("submit", function() {
			jQuery(this).find("input[type=submit]").attr("disabled", "disabled");
		});			jQuery("#ajax").bind("submit", function() { jQuery("#ajax .alert-error").remove();				jQuery.ajax({
					url: @json(route('book.contact.envoyer', ['login' => $b->us_dir])),
					type: "post",
					data: jQuery("#ajax").serialize(),
					success: function(response) {
						if(response != undefined && typeof response == "object" && response.errors) {		var errorSize = response.errors.length;
		var errorHTML = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a><ul>';
		for(e = 0; e < errorSize; ++e)
			errorHTML += '<li>' + jQuery('<div>').text(response.errors[e]).html() + '</li>';
		errorHTML += '</ul></div>';
		jQuery("#ajax").prepend(errorHTML);
		jQuery("#ajax-captcha-img").attr("src", @json(route('book.captcha', ['login' => $b->us_dir, 'formulaire' => 'contact_book'])) + "?" + Date.now()); jQuery("#ajax-element-captcha").val("");							jQuery("html, body").animate({ scrollTop: jQuery("#ajax").offset().top }, 500 );
						}
						else {contactForm_callback(response);						}
						jQuery("#ajax").find("input[type=submit]").removeAttr("disabled");
					}
				});
				return false;
			});}); </script>

				<script type="text/javascript">
				var contactForm_callback = function(data) {
				    var obj = typeof data == "string" ? JSON.parse(data) : data;

				    if (obj.error) {
				       obj.message = @json(__('Erreur de traitement, recharger la page, svp...'));
				    } else {
				        obj.message = @json('<h3>'.__('Message envoyé.').'</h3><br/>'.__('Vous allez reçevoir une notification par mail (penser à vérifier votre boite spam)'));
				    }
					$("#contact_box").html("<div id=\"form_retour\">"+ obj.message +"</div>");
				}
				</script>
