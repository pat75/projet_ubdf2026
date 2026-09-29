{{-- Pied de page du portail : le pied commun, puis le bandeau cookies et la fermeture des conteneurs ouverts dans partials/header. --}}
@include('partials.pied-commun')

<!-- cookies -->
<div id="cookie-policy" x-data="bandeauCookies" :class="{ show: visible }" @click="accepter">
    <div class="btn_close close">
        <div></div>
    </div>
    <div class="cookiepolicy-message">
        <p>Nous utilisons des cookies pour améliorer notre site et votre expérience de navigation.
            En utilisant notre site, vous acceptez notre politique de cookies. <span><a target="_blank" href="/doc/mentions-legales">En savoir plus</a></span>
        </p>
    </div>
</div>

</div>
