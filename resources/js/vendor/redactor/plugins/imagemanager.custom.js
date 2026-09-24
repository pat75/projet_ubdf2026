/*
 * Bibliotheque d'images de l'editeur des pages (App\Livewire\Espace\Pages).
 *
 * Le Redactor 3.5.2 recupere du legacy ne livre pas de vrai gestionnaire
 * d'images malgre le nom du plugin cite dans sa config d'origine
 * (ub_usadmin_core.js) : ni fichier plugin correspondant, ni UI de liste
 * dans le coeur. Ce plugin construit celle-ci, dans le style et selon les
 * conventions des plugins Redactor livres (voir video.min.js) :
 *   - un bouton juste apres celui d'insertion d'image ;
 *   - une fenetre modale qui charge la liste (opts.imagemanagerListe),
 *     avec upload (opts.imageUpload, deja cable pour le glisser-deposer),
 *     remplacement et suppression (opts.imagemanagerAction, gabarit
 *     d'URL avec "__ID__" a la place de l'identifiant) ;
 *   - un clic sur une vignette insere l'image a l'endroit du curseur, via
 *     la meme API que l'upload direct (module.image.insert), pour qu'elle
 *     beneficie du meme comportement (redimensionnement, legende...).
 */
(function () {
    Redactor.add('plugin', 'imagemanager', {
        translations: {
            fr: {
                imagemanager: {
                    library: 'Bibliothèque',
                    'library-title': 'Bibliothèque d’images',
                    add: 'Ajouter une image',
                    empty: 'Aucune image déposée pour le moment.',
                    loading: 'Chargement…',
                    error: 'Impossible de charger la bibliothèque.',
                    replace: 'Remplacer',
                    delete: 'Supprimer',
                    close: 'Fermer',
                    'confirm-delete': 'Supprimer cette image ? Les pages qui l’utilisent encore afficheront une image cassée.',
                },
            },
        },

        modals: {
            imagemanager: '<div class="redactor-imagemanager">'
                + '<div class="redactor-imagemanager-liste" data-imagemanager-liste></div>'
                + '</div>',
        },

        init(app) {
            this.app = app;
            this.opts = app.opts;
            this.lang = app.lang;
            this.toolbar = app.toolbar;
            this.insertion = app.insertion;
        },

        start() {
            if (!this.opts.imagemanagerListe) {
                return;
            }

            this.toolbar.addButtonAfter('image', 'imagemanager', {
                title: this.lang.get('imagemanager.library'),
                api: 'plugin.imagemanager.open',
            }).setIcon('<i class="re-icon-imagemanager">'
                + '<svg viewBox="0 0 20 20" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg">'
                + '<rect x="2" y="3" width="12" height="12" rx="1.5" stroke="currentColor" stroke-width="1.4"/>'
                + '<rect x="6" y="7" width="12" height="12" rx="1.5" fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="1.4"/>'
                + '<circle cx="9.5" cy="10.5" r="1.1" fill="currentColor"/>'
                + '<path d="M7 15l2.6-2.6a1 1 0 0 1 1.4 0L14 15.4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>'
                + '</svg></i>');
        },

        open() {
            this.app.api('module.modal.build', {
                title: this.lang.get('imagemanager.library-title'),
                width: '620px',
                name: 'imagemanager',
                commands: { close: { title: this.lang.get('imagemanager.close') } },
            });
        },

        onmodal: {
            imagemanager: {
                opened: function (e, modal) {
                    this.charger(modal.find('[data-imagemanager-liste]').get());
                },
            },
        },

        // Recharge la liste (apres ajout, remplacement ou suppression) sans
        // fermer la fenetre.
        async charger(conteneur) {
            conteneur.innerHTML = '<p class="redactor-imagemanager-message">' + this.lang.get('imagemanager.loading') + '</p>';

            try {
                const reponse = await fetch(this.opts.imagemanagerListe, { headers: { Accept: 'application/json' } });
                const images = await reponse.json();
                this.afficher(conteneur, images);
            } catch (erreur) {
                conteneur.innerHTML = '<p class="redactor-imagemanager-message">' + this.lang.get('imagemanager.error') + '</p>';
            }
        },

        afficher(conteneur, images) {
            conteneur.innerHTML = '';

            const ajout = document.createElement('label');
            ajout.className = 'redactor-imagemanager-ajouter';
            ajout.innerHTML = '<span>+</span><span>' + this.lang.get('imagemanager.add') + '</span>'
                + '<input type="file" accept="image/jpeg,image/png,image/gif,image/webp" hidden>';
            ajout.querySelector('input').addEventListener('change', (e) => {
                if (e.target.files[0]) {
                    this.envoyer(e.target.files[0], conteneur);
                }
            });
            conteneur.appendChild(ajout);

            if (!images.length) {
                const vide = document.createElement('p');
                vide.className = 'redactor-imagemanager-message';
                vide.textContent = this.lang.get('imagemanager.empty');
                conteneur.appendChild(vide);

                return;
            }

            const grille = document.createElement('div');
            grille.className = 'redactor-imagemanager-grid';
            images.forEach((image) => grille.appendChild(this.carte(image, conteneur)));
            conteneur.appendChild(grille);
        },

        carte(image, conteneur) {
            const carte = document.createElement('div');
            carte.className = 'redactor-imagemanager-carte';

            const apercu = document.createElement('button');
            apercu.type = 'button';
            apercu.className = 'redactor-imagemanager-apercu';
            apercu.title = image.nom || '';
            apercu.style.backgroundImage = 'url(' + image.url + ')';
            apercu.addEventListener('click', () => this.inserer(image));

            const legende = document.createElement('div');
            legende.className = 'redactor-imagemanager-legende';
            legende.textContent = image.creeeLe;

            const actions = document.createElement('div');
            actions.className = 'redactor-imagemanager-actions';

            const remplacer = document.createElement('label');
            remplacer.className = 'redactor-imagemanager-action';
            remplacer.title = this.lang.get('imagemanager.replace');
            remplacer.innerHTML = '↻<input type="file" accept="image/jpeg,image/png,image/gif,image/webp" hidden>';
            remplacer.querySelector('input').addEventListener('change', (e) => {
                if (e.target.files[0]) {
                    this.remplacer(image, e.target.files[0], conteneur);
                }
            });

            const supprimer = document.createElement('button');
            supprimer.type = 'button';
            supprimer.className = 'redactor-imagemanager-action';
            supprimer.title = this.lang.get('imagemanager.delete');
            supprimer.textContent = '✕';
            supprimer.addEventListener('click', () => {
                if (window.confirm(this.lang.get('imagemanager.confirm-delete'))) {
                    this.supprimer(image, conteneur);
                }
            });

            actions.append(remplacer, supprimer);
            carte.append(apercu, legende, actions);

            return carte;
        },

        inserer(image) {
            this.app.api('module.modal.close');
            this.app.api('module.image.insert', { file: { url: image.url, id: image.id } });
        },

        async envoyer(fichier, conteneur) {
            const donnees = new FormData();
            donnees.append('file', fichier);
            Object.entries(this.opts.uploadData || {}).forEach(([cle, valeur]) => donnees.append(cle, valeur));

            await fetch(this.opts.imageUpload, { method: 'POST', body: donnees, headers: { Accept: 'application/json' } });
            this.charger(conteneur);
        },

        async remplacer(image, fichier, conteneur) {
            const donnees = new FormData();
            donnees.append('file', fichier);
            Object.entries(this.opts.uploadData || {}).forEach(([cle, valeur]) => donnees.append(cle, valeur));

            await fetch(this.opts.imagemanagerAction.replace('__ID__', image.id), { method: 'POST', body: donnees, headers: { Accept: 'application/json' } });
            this.charger(conteneur);
        },

        async supprimer(image, conteneur) {
            await fetch(this.opts.imagemanagerAction.replace('__ID__', image.id), {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': (this.opts.uploadData || {})._token },
            });
            this.charger(conteneur);
        },
    });
})();
