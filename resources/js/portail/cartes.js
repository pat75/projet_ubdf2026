/*
 * Cartes de books du portail (<x-book-card>), ex-book.book_static_show,
 * ub_infinit (js_core_pages.js, js_core_cards.js) et public/js/ubdf-infinite.js.
 *
 * - Les cartes masquees (newitem_hide) apparaissent en cascade, 40 ms
 *   d'ecart ; la transition est dans le CSS du front 2018.
 * - Un clic sur une carte ouvre sa visionneuse (sauf le lien de metier).
 *   Delegue au document : les cartes arrivees par defilement en profitent
 *   sans rien reactiver.
 * - #login dans l'URL rouvre la visionneuse de ce book s'il est affiche ;
 *   #create-book ouvre l'inscription.
 * - Un element [data-contacter="login"] ouvre la visionneuse de ce book
 *   directement sur son formulaire de contact (page image).
 * - Defilement infini (categories, recherche) : la page declare ou lire
 *   la suite dans window.ubdf (cartes_url, cartes_params).
 */
function reveler(cartes) {
    cartes.forEach((carte, i) => setTimeout(() => carte.classList.remove('newitem_hide'), i * 40));
}

/*
 * #login : la carte du book est ouverte si elle est dans la page ; sinon
 * (elle n'arriverait qu'au defilement), elle est demandee seule au serveur,
 * rendue par le meme composant, et la visionneuse s'ouvre avec elle.
 */
async function ouvrirDepuisAncre(Alpine, login) {
    let carte = document.getElementById(`user_${login}`);

    if (!carte) {
        try {
            const reponse = await fetch(`/carte/${encodeURIComponent(login)}`, { headers: { Accept: 'application/json' } });
            if (!reponse.ok) return;
            const gabarit = document.createElement('template');
            gabarit.innerHTML = (await reponse.json()).html; // HTML rendu par le serveur (partials.cartes)
            carte = gabarit.content.querySelector('.ui.card');
        } catch {
            return;
        }
    }

    if (carte?.dataset.slider) Alpine.store('visionneuse').ouvrir(carte);
}

export default function cartes(Alpine) {
    document.addEventListener('click', (e) => {
        const carte = e.target.closest('.ui.card[data-user][data-slider]');
        if (!carte || e.target.closest('.meta a')) return;
        e.preventDefault();
        Alpine.store('visionneuse').ouvrir(carte);
    });

    document.addEventListener('click', async (e) => {
        const bouton = e.target.closest('[data-contacter]');
        if (!bouton) return;
        e.preventDefault();
        await ouvrirDepuisAncre(Alpine, bouton.dataset.contacter);
        const visionneuse = Alpine.store('visionneuse');
        if (visionneuse.ouverte) visionneuse.contacter();
    });

    document.addEventListener('alpine:initialized', () => {
        reveler([...document.querySelectorAll('.ptf_index_static.newitem_hide')]);

        const ancre = decodeURIComponent(window.location.hash.slice(1));
        if (ancre === 'create-book') {
            // Ancien lien vers la fenetre d'inscription, devenue une page.
            window.location.href = window.ubdf?.inscription ?? '/creer-un-book';
        } else if (/^[a-z0-9_-]+$/i.test(ancre)) {
            ouvrirDepuisAncre(Alpine, ancre);
        }
    });

    Alpine.data('defilementInfini', () => ({
        page: 0,
        enCours: false,
        termine: false,

        init() {
            const conf = window.ubdf ?? {};
            if (!conf.cartes_url) {
                this.termine = true;
                return;
            }
            // Charge quand le bas de la grille approche (600 px, comme avant).
            const observateur = new IntersectionObserver(([entree]) => entree.isIntersecting && this.charger(), {
                rootMargin: '0px 0px 600px 0px',
            });
            observateur.observe(this.$refs.fin);
        },

        async charger() {
            if (this.enCours || this.termine) return;
            this.enCours = true;
            const conf = window.ubdf;
            const url = new URL(`${conf.cartes_url}/${this.page + 1}`, window.location.origin);
            Object.entries(conf.cartes_params ?? {}).forEach(([cle, valeur]) => url.searchParams.set(cle, valeur));

            try {
                const reponse = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!reponse.ok) throw new Error(reponse.status);
                const donnees = await reponse.json();
                this.page += 1;

                if (donnees.count > 0) {
                    const gabarit = document.createElement('template');
                    gabarit.innerHTML = donnees.html; // HTML rendu par le serveur (partials.cartes)
                    const nouvelles = [...gabarit.content.querySelectorAll('.ui.card')];
                    document.getElementById('position_card_last').before(gabarit.content);
                    reveler(nouvelles);
                }
                this.termine = !!donnees.fin;
            } catch {
                // Page ratee : la suivante sera retentee au prochain passage.
            } finally {
                this.enCours = false;
            }

            // Des cartes trop peu nombreuses pour rallonger la page ne
            // relancent aucune intersection : on revérifie.
            if (!this.termine) {
                requestAnimationFrame(() => {
                    if (this.$refs.fin.getBoundingClientRect().top < window.innerHeight + 600) this.charger();
                });
            }
        },
    }));
}
