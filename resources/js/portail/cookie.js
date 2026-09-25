/*
 * Lecture et ecriture d'un cookie du portail, a la place de $.cookie
 * (jquery.cookie, embarque dans js_allplug2018.js).
 */
export function lireCookie(nom) {
    const trouve = document.cookie.split('; ').find((c) => c.startsWith(encodeURIComponent(nom) + '='));

    return trouve ? decodeURIComponent(trouve.split('=').slice(1).join('=')) : null;
}

export function ecrireCookie(nom, valeur, jours) {
    const expire = new Date(Date.now() + jours * 864e5).toUTCString();
    document.cookie = `${encodeURIComponent(nom)}=${encodeURIComponent(valeur)}; expires=${expire}; path=/; SameSite=Lax`;
}
