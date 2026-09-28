{{-- Formulaire de contact du creatif, a la place du diaporama de la
     visionneuse ($store.visionneuse.contactOuvert) : meme principe que le
     formulaire de contact du book (book/commun/_formulaire-contact), une
     ligne soulignee par champ, mais sur le fond sombre de la visionneuse.
     Logique : resources/js/portail/contact.js. --}}
@php
    $champ = 'w-full border-0 border-b border-white/30 bg-transparent px-0 py-2 text-[16px] font-light text-white outline-none placeholder:text-white/50 focus:border-white focus:ring-0';
@endphp
<div id="visionneuse_contact" x-show="$store.visionneuse.contactOuvert" x-cloak x-data="contactCreatif"
     x-transition.opacity.duration.300ms>
    <style>
        #visionneuse_contact {
            position: absolute; top: 230px; left: 0; right: 0; bottom: 70px; z-index: 1;
            display: flex; align-items: flex-start; justify-content: center;
            overflow-y: auto; padding: 10px 20px 20px;
        }
        #visionneuse_contact .visionneuse_contact_carte {
            width: 100%; max-width: 440px; background: rgba(10, 10, 10, .92);
            border-radius: 8px; padding: 28px 28px 24px; box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
        }
        #visionneuse_contact .visionneuse_contact_titre { position: relative; }
        #visionneuse_contact .visionneuse_contact_retour {
            position: absolute; right: calc(100% + 45px); top: 50%; transform: translateY(-50%); white-space: nowrap;
        }
        #visionneuse_contact .visionneuse_contact_captcha { display: flex; flex-wrap: nowrap; align-items: center; gap: 10px; }
        #visionneuse_contact .visionneuse_contact_captcha img { flex-shrink: 0; }
        #visionneuse_contact .visionneuse_contact_captcha input { flex: 1 1 auto; min-width: 0; width: auto; }
        @@media (max-width: 767px) {
            /* Cale sur l'ecran (fixed) : le conteneur legacy est decale et
               plus etroit que la page, la carte debordait a droite. */
            #visionneuse_contact { position: fixed; top: 290px; left: 0; right: 0; bottom: 0; padding: 0 14px 24px; }
            #visionneuse_contact .visionneuse_contact_carte { padding: 22px 18px 20px; }
            #visionneuse_contact .visionneuse_contact_retour { position: static; transform: none; margin-bottom: 10px !important; }
            #visionneuse_contact .visionneuse_contact_captcha img { width: 120px; height: 40px; }
        }
    </style>

    <div class="visionneuse_contact_carte">

        <div x-show="vue === 'merci'" x-cloak x-transition.opacity class="py-10 text-center">
            <p class="flex items-center justify-center gap-3 text-[22px] font-semibold text-white">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                {{ __('Merci') }}
            </p>
            <p class="mt-2 text-[16px] font-light text-white/70">{{ __('Votre demande vient d’être envoyée') }}</p>
        </div>

        <form x-show="vue !== 'merci'" x-ref="formulaire" novalidate @submit.prevent="envoyer($el)" class="flex flex-col gap-8">
            <input type="hidden" name="action" value="work_A_contact">
            <input type="hidden" name="mf_request_detail" value="">
            <input type="hidden" name="us_dir" :value="book.login">
            <input type="hidden" name="us_key" :value="book.fiche.book_key">
            <input type="hidden" name="us_book_visuel" :value="visuel">

            {{-- « Retour » dans la marge, sur la ligne de la phrase et 45px a sa
                 gauche ; au-dessus d'elle sur mobile (pas de marge assez large). --}}
            <div class="visionneuse_contact_titre">
                <button type="button" @click="book.contactOuvert = false" title="{{ __('Revenir au diaporama') }}"
                        style="background:none; border:0; padding:0; margin:0; color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:4px; font-size:13px;"
                        class="visionneuse_contact_retour transition hover:opacity-70">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
                    {{ __('Retour') }}
                </button>
                <p class="text-[22.5px] font-light text-white/70" style="margin:0;" x-text="'Je souhaite contacter ' + (book.fiche.book_prenom_nom || '')"></p>
            </div>

            <div x-show="vue === 'erreur'" x-cloak>
                <ul class="border-l-2 border-red-500 pl-4 text-[14px] text-red-400">
                    <template x-for="message in erreurs.liste ?? []"><li x-text="message"></li></template>
                </ul>
                <button type="button" class="mt-2 text-[13px] underline text-white/70" @click="retour()">{{ __('Retour') }}</button>
            </div>

            <div x-show="vue !== 'erreur'">
                <textarea id="visionneuse-contact-message" name="us_message" rows="3" placeholder="{{ __('Message') }}"
                          @input="erreurs.us_message = ''" class="{{ $champ }} resize-none"></textarea>
                <p x-show="erreurs.us_message" x-text="erreurs.us_message" x-cloak class="mt-2 text-[13px] text-red-400"></p>
            </div>

            <div x-show="vue !== 'erreur'">
                <input id="visionneuse-contact-nom" name="us_nom_prenom" type="text" placeholder="{{ __('Nom et prénom') }}"
                       autocomplete="name" @input="erreurs.us_nom_prenom = ''" class="{{ $champ }}">
                <p x-show="erreurs.us_nom_prenom" x-text="erreurs.us_nom_prenom" x-cloak class="mt-2 text-[13px] text-red-400"></p>
            </div>

            <div x-show="vue !== 'erreur'">
                <input id="visionneuse-contact-mail" name="us_mail" type="email" placeholder="{{ __('E-mail') }}"
                       autocomplete="email" @input="erreurs.us_mail = ''" class="{{ $champ }}">
                <p x-show="erreurs.us_mail" x-text="erreurs.us_mail" x-cloak class="mt-2 text-[13px] text-red-400"></p>
            </div>

            <div x-show="vue !== 'erreur'" class="mt-2">
                <div class="visionneuse_contact_captcha">
                    {{-- Image servie en noir sur blanc (/captcha_img) : inversee en
                         blanc sur noir par filtre, pour le fond sombre de la visionneuse. --}}
                    <img :src="captcha" alt="{{ __('Code à recopier') }}" title="{{ __('Cliquez pour changer') }}"
                         width="150" height="50" style="border:1px solid rgba(255,255,255,.3); border-radius:3px; cursor:pointer; background:#000; filter:invert(1);"
                         @click="rechargerCaptcha()">
                    <button type="button" @click="rechargerCaptcha()" title="{{ __('Nouvelle image') }}" aria-label="{{ __('Nouvelle image') }}"
                            style="background:none; border:0; box-shadow:none; padding:4px; margin:0; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;"
                            class="shrink-0 transition hover:opacity-70">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 12a9 9 0 1 1-2.64-6.36"/>
                            <path d="M21 3v6h-6"/>
                        </svg>
                    </button>
                    <input name="captcha_answer" type="text" maxlength="4" placeholder="{{ __('recopiez le code') }}" autocomplete="off"
                           style="letter-spacing:2px; text-transform:uppercase;" @input="erreurs.captcha_answer = ''" class="{{ $champ }} w-32">
                </div>
                <p x-show="erreurs.captcha_answer" x-text="erreurs.captcha_answer" x-cloak class="mt-2 text-[13px] text-red-400"></p>
            </div>

            <div x-show="vue !== 'erreur'" style="margin-top:10px;">
                <button type="submit" :disabled="vue === 'envoi'" style="border:0;"
                        class="inline-flex items-center gap-2 bg-white px-8 py-3 text-[13px] font-semibold uppercase tracking-[1.3px] text-black transition hover:opacity-80 disabled:opacity-60">
                    <svg x-show="vue === 'envoi'" x-cloak class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ __('Envoyer') }}
                </button>
            </div>
        </form>
    </div>
</div>
