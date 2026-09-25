import Alpine from 'alpinejs';

/*
 * Apparition au defilement, sur le principe des animations de l'accueil
 * Tesli (AOS, `data-aos="fade-up"` + `data-aos-delay`, rejouees a chaque
 * passage) — ici sans bibliotheque, en directive Alpine et classes
 * Tailwind :
 *
 *   x-apparition                monte en fondu (par defaut)
 *   x-apparition:gauche         arrive de la gauche
 *   x-apparition:droite         arrive de la droite
 *   x-apparition:zoom           grossit legerement en fondu
 *   x-apparition:gauche.150     ... apres 150 ms (decalage en cascade)
 *
 * L'element se cache de nouveau quand il repasse sous le bas de l'ecran :
 * l'animation rejoue a la redescente, sans clignoter quand on remonte.
 * Rien ne bouge si le visiteur a demande a limiter les animations.
 */
// En `!important` (suffixe `!`) : les feuilles Semantic UI du portail ne sont
// pas en couche CSS et l'emporteraient (`.ui.card` redefinit `transition`).
const departs = {
    haut: ['opacity-0!', 'translate-y-10!'],
    gauche: ['opacity-0!', '-translate-x-16!'],
    droite: ['opacity-0!', 'translate-x-16!'],
    zoom: ['opacity-0!', 'scale-95!'],
};

const caches = new WeakMap();

const observateur = new IntersectionObserver((entrees) => {
    entrees.forEach(({ target, isIntersecting, boundingClientRect }) => {
        const { depart, delai } = caches.get(target);

        if (isIntersecting) {
            // Le decalage ne vaut que pour l'apparition : retire ensuite,
            // il retarderait les effets de survol du theme.
            target.style.setProperty('transition-delay', `${delai}ms`, 'important');
            target.classList.remove(...depart);
            setTimeout(() => target.style.removeProperty('transition-delay'), delai + 700);
        } else if (boundingClientRect.top > 0) {
            target.classList.add(...depart);
        }
    });
}, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

Alpine.directive('apparition', (el, { value, modifiers }, { cleanup }) => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const depart = departs[value] ?? departs.haut;
    const delai = Number(modifiers.find((m) => /^\d+$/.test(m)) ?? 0);

    caches.set(el, { depart, delai });
    el.classList.add('transition!', 'duration-700!', 'ease-[cubic-bezier(.22,.61,.36,1)]!', ...depart);

    observateur.observe(el);
    cleanup(() => observateur.unobserve(el));
});

window.Alpine = Alpine;
Alpine.start();
