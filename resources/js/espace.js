import Sortable from 'sortablejs';
import newsletter from './portail/newsletter';
import statistiques from './espace/statistiques';
import './vendor/redactor/redactor.min.js';
import './vendor/redactor/redactor.min.css';
// Plugins charges apres le coeur : ils s'enregistrent sur le meme global
// (window.Redactor / window.$R), comme dans la version legacy du book.
import './vendor/redactor/plugins/alignment.min.js';
import './vendor/redactor/plugins/fontfamily.min.js';
import './vendor/redactor/plugins/fontcolor.min.js';
import './vendor/redactor/plugins/fontsize.min.js';
import './vendor/redactor/plugins/imageposition.min.js';
import './vendor/redactor/plugins/video.min.js';
import './vendor/redactor/plugins/fullscreen.min.js';
import './vendor/redactor/plugins/imagemanager.custom.js';

/*
 * Editeur de texte des pages (App\Livewire\Espace\Pages) : Redactor 3.5.2
 * (licence Imperavi, deja utilisee par la version legacy du book), avec la
 * meme palette d'outils que le legacy (voir ub_usadmin_core.js) : mise en
 * forme, polices, couleurs, images deposees ou collees, videos, liens,
 * source HTML, plein ecran. Seul « textia » (assistant IA maison du
 * legacy, pas un plugin Redactor standard) n'est pas repris.
 */
window.espacePageEditor = (el, surChangement, urls) => {
    const jeton = document.querySelector('meta[name="csrf-token"]')?.content;

    const app = window.$R(el, {
        lang: 'fr',
        buttons: ['format', 'bold', 'italic', 'alignment', 'fontfamily', 'fontcolor', 'fontsize', 'image', 'video', 'link', 'horizontalrule', 'html'],
        plugins: ['alignment', 'fontfamily', 'fontcolor', 'fontsize', 'imageposition', 'video', 'fullscreen', 'imagemanager'],
        fontfamily: ['Montserrat', 'HKGrotesk', 'Dosis', 'Lato', 'Arial', 'Verdana', 'Times New Roman'],
        source: true,

        // Deposees dans l'editeur, collees, ou choisies dans la bibliotheque
        // (plugin imagemanager.custom.js) : la meme image finit dans
        // img_cms/ du book (App\Services\Espace\DepotImagePage), a cote
        // des declinaisons de portfolio mais hors de leur quota. Les URL
        // viennent de route() cote Blade : elles doivent suivre le prefixe
        // de langue eventuel.
        imageUpload: urls.upload,
        imagemanagerListe: urls.liste,
        imagemanagerAction: urls.action,
        imageResizable: true,
        imagePosition: true,
        uploadData: { _token: jeton },

        callbacks: {
            // changed : a chaque modification du contenu (frappe, collage,
            // mise en forme, image inseree).
            // `app` est capture par la fermeture, pas par `this` (dont le
            // contexte d'appel n'est pas garanti par Redactor).
            changed: () => surChangement(app.source.getCode()),
        },
    });

    return app;
};

/*
 * Rend les enfants [data-id] de `el` deplacables ; `envoyer` recoit le
 * nouvel ordre des identifiants. `data-poignee` sur `el` designe la
 * poignee quand la liste en contient d'autres, imbriquees.
 */
window.espaceTri = (el, envoyer) => Sortable.create(el, {
    handle: el.dataset.poignee ?? (el.querySelector('[data-poignee]') ? '[data-poignee]' : undefined),
    animation: 150,
    onEnd: () => envoyer([...el.querySelectorAll(':scope > [data-id]')].map((n) => n.dataset.id)),
});

// x-espace-tri="methode" : raccourci pour une methode Livewire du composant.
document.addEventListener('alpine:init', () => {
    window.Alpine.directive('espace-tri', (el, { expression }) => {
        window.espaceTri(el, (ids) => {
            window.Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id')).call(expression, ids);
        });
    });

    /*
     * x-espace-visuels (data-galerie="id") : les visuels d'un portfolio, a
     * trier sur place ou a glisser dans un autre portfolio (meme groupe).
     *
     * D'une liste a l'autre, l'element est remis a sa place avant l'appel :
     * c'est le rendu Livewire qui l'installe dans sa nouvelle liste, sans
     * se heurter au deplacement deja fait par Sortable.
     */
    window.Alpine.directive('espace-visuels', (el) => {
        const ids = (liste) => [...liste.querySelectorAll(':scope > [data-id]')].map((n) => n.dataset.id);
        const composant = () => window.Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id'));

        Sortable.create(el, {
            group: 'visuels',
            handle: '[data-poignee]',
            animation: 150,
            onEnd: (evt) => {
                if (evt.from === evt.to) {
                    if (evt.oldIndex !== evt.newIndex) {
                        composant().call('ordonnerVisuels', Number(el.dataset.galerie), ids(el));
                    }

                    return;
                }

                const ordre = ids(evt.to);
                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] ?? null);
                composant().call('deplacerVisuel', Number(evt.item.dataset.id), Number(evt.to.dataset.galerie), ordre);
            },
        });
    });
});

/*
 * Recadrage de la photo de profil, en carre : glisser-deposer ou clic pour
 * choisir un fichier, deplacement et zoom dans une fenetre ronde, puis
 * export du canvas en JPEG — c'est ce blob, deja recadre, qui part vers
 * Habillage::deposerAvatar() ($wire.upload). Le canvas est carre ; le rond
 * de l'apercu n'est qu'un habillage CSS (border-radius), retrouve partout
 * ailleurs ou la photo s'affiche (object-cover + rounded-full).
 */
function recadrageAvatar() {
    return {
        survole: false,
        ouvert: false,
        zoom: 1,
        panX: 0,
        panY: 0,
        taille: 256,
        image: null,
        echelleBase: 1,
        enCours: false,
        depart: { x: 0, y: 0, panX: 0, panY: 0 },

        fichierDepose(fichier) {
            if (! fichier || ! fichier.type.startsWith('image/')) {
                return;
            }

            const lecteur = new FileReader();
            lecteur.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    this.image = img;
                    this.echelleBase = Math.max(this.taille / img.width, this.taille / img.height);
                    this.zoom = 1;
                    this.panX = 0;
                    this.panY = 0;
                    this.ouvert = true;
                    this.$nextTick(() => this.redessiner());
                };
                img.src = e.target.result;
            };
            lecteur.readAsDataURL(fichier);
        },

        echelle() {
            return this.echelleBase * this.zoom;
        },

        limites() {
            const e = this.echelle();

            return {
                x: Math.max(0, (this.image.width * e - this.taille) / 2),
                y: Math.max(0, (this.image.height * e - this.taille) / 2),
            };
        },

        redessiner() {
            if (! this.image) {
                return;
            }

            const limites = this.limites();
            this.panX = Math.min(limites.x, Math.max(-limites.x, this.panX));
            this.panY = Math.min(limites.y, Math.max(-limites.y, this.panY));

            this.dessiner(this.$refs.canvas, 1);
        },

        // Meme cadrage a n'importe quelle resolution : `facteur` agrandit
        // l'apercu (256 px) pour l'export, sans changer ce qu'il montre.
        dessiner(canvas, facteur) {
            const e = this.echelle() * facteur;
            const l = this.image.width * e;
            const h = this.image.height * e;
            const cote = this.taille * facteur;
            const ctx = canvas.getContext('2d');

            // Fond blanc : dezoomee (jusqu'a 70 %), l'image ne couvre plus
            // tout le cadre, et le JPEG exporte rendrait le vide en noir.
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, cote, cote);
            ctx.drawImage(this.image, cote / 2 - l / 2 + this.panX * facteur, cote / 2 - h / 2 + this.panY * facteur, l, h);
        },

        debuterGlisser(evt) {
            if (! this.image) {
                return;
            }

            this.enCours = true;
            this.depart = { x: evt.clientX, y: evt.clientY, panX: this.panX, panY: this.panY };
        },

        glisser(evt) {
            if (! this.enCours) {
                return;
            }

            this.panX = this.depart.panX + (evt.clientX - this.depart.x);
            this.panY = this.depart.panY + (evt.clientY - this.depart.y);
            this.redessiner();
        },

        terminerGlisser() {
            this.enCours = false;
        },

        zoomerMolette(evt) {
            if (! this.image) {
                return;
            }

            this.zoom = Math.min(3, Math.max(0.7, this.zoom - evt.deltaY / 500));
            this.redessiner();
        },

        fermer() {
            this.ouvert = false;
            this.image = null;
            if (this.$refs.entree) {
                this.$refs.entree.value = '';
            }
        },

        valider() {
            // 256 x 4 = 1024 px, le cote que garde DepotAvatar : rien n'est agrandi.
            const facteur = 4;
            const sortie = document.createElement('canvas');
            sortie.width = sortie.height = this.taille * facteur;
            this.dessiner(sortie, facteur);

            sortie.toBlob((blob) => {
                const fichier = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                this.$wire.upload('avatarTemp', fichier, () => {
                    this.$wire.call('deposerAvatar');
                    this.fermer();
                }, () => this.fermer());
            }, 'image/jpeg', 0.9);
        },
    };
}

/*
 * Edition sur place d'un texte (x-espace.champ-editable) : pas de champ
 * visible, un crayon juste apres le texte. Clic sur le texte ou le
 * crayon : le texte devient editable (contenteditable), souligne en vert,
 * et le crayon disparait. Sortie du texte ou Entree (une ligne) :
 * enregistrerChamp(nom, valeur) sur le composant Livewire parent (trait
 * EnregistreChamps), si la valeur a change ; le texte passe en douceur au vert
 * pendant l'enregistrement. Echap annule. Succes : coche 4 s, puis crayon.
 */
function champEditable(nom, valeur, multiligne) {
    return {
        nom,
        valeur,
        multiligne,
        edition: false,
        enregistrement: false,
        valide: false,
        erreur: null,
        minuteur: null,

        editer() {
            if (this.edition) {
                return;
            }

            this.edition = true;
            this.erreur = null;
            this.$nextTick(() => {
                const el = this.$refs.texte;
                el.focus();

                // Curseur en fin de texte, sans rien selectionner.
                const plage = document.createRange();
                plage.selectNodeContents(el);
                plage.collapse(false);
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(plage);
            });
        },

        // Un texte vide laisse souvent un <br> : on le retire, pour que
        // :empty affiche de nouveau l'invite.
        nettoyer() {
            if (this.$refs.texte.innerText.trim() === '') {
                this.$refs.texte.textContent = '';
            }
        },

        touche(evt) {
            if (evt.key === 'Escape') {
                evt.preventDefault();
                this.$refs.texte.textContent = this.valeur;
                this.edition = false;
                this.$refs.texte.blur();
            } else if (evt.key === 'Enter' && ! this.multiligne) {
                evt.preventDefault();
                this.$refs.texte.blur();
            }
        },

        async valider() {
            if (! this.edition) {
                return;
            }

            this.edition = false;

            const brut = this.$refs.texte.innerText.replace(/\n$/, '');
            const texte = this.multiligne ? brut : brut.replace(/\s*\n\s*/g, ' ');

            // Le navigateur laisse souvent un saut de ligne ou un <br> en fin
            // de contenteditable : on reaffiche le texte nettoye, sinon le
            // crayon tombe sur une ligne vide.
            this.$refs.texte.textContent = texte;

            if (texte === this.valeur) {
                return;
            }

            // La vague verte dure au moins le temps de son animation, meme
            // si le serveur repond plus vite : sinon il ne se voit pas.
            this.enregistrement = true;
            const vague = new Promise((fin) => setTimeout(fin, 1200));

            try {
                const [reponse] = await Promise.all([this.$wire.enregistrerChamp(this.nom, texte), vague]);

                if (reponse?.erreur) {
                    this.erreur = reponse.erreur;

                    return;
                }

                this.valeur = texte;
                this.valide = true;
                clearTimeout(this.minuteur);
                this.minuteur = setTimeout(() => this.valide = false, 4000);
            } catch {
                this.erreur = 'Enregistrement impossible, réessayez.';
            } finally {
                this.enregistrement = false;
            }
        },
    };
}

/*
 * Photo de profil changee ou retiree (Habillage::annoncerAvatar) : toutes
 * les vignettes [data-avatar-profil] de la page suivent, photo ou
 * initiales, sans recharger. Classes et styles de chaque vignette sont
 * gardes, pour qu'elle conserve sa taille et sa forme.
 */
window.addEventListener('avatar-profil-modifie', ({ detail: { url, initiales, couleur } }) => {
    document.querySelectorAll('[data-avatar-profil]').forEach((ancien) => {
        let nouveau;

        if (url) {
            nouveau = ancien.tagName === 'IMG' ? ancien : document.createElement('img');
            nouveau.src = url;
            nouveau.alt = ancien.getAttribute('alt') ?? '';
            nouveau.style.objectFit = 'cover';
            nouveau.textContent = '';
        } else {
            nouveau = ancien.tagName === 'SPAN' ? ancien : document.createElement('span');
            nouveau.textContent = initiales;
            nouveau.setAttribute('aria-hidden', 'true');
            Object.assign(nouveau.style, {
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                color: '#fff', fontWeight: '700', backgroundColor: couleur,
            });
        }

        if (nouveau !== ancien) {
            nouveau.className = ancien.className;
            nouveau.style.cssText = ancien.style.cssText + nouveau.style.cssText;
            nouveau.setAttribute('data-avatar-profil', '');
            ancien.replaceWith(nouveau);
        }
    });
});

document.addEventListener('alpine:init', () => {
    window.Alpine.data('recadrageAvatar', recadrageAvatar);
    window.Alpine.data('champEditable', champEditable);
    window.Alpine.data('statistiques', statistiques);

    /* Menu plein ecran du portail (<x-portail.menu-plein-ecran>), ouvert
       par le burger de l'entete sur mobile. Meme store que le portail,
       sans les classes Semantic que ce dernier bascule en plus. */
    window.Alpine.store('menu', {
        ouvert: false,
        basculer() {
            this.ouvert = !this.ouvert;
        },
    });
    newsletter(window.Alpine);
});

