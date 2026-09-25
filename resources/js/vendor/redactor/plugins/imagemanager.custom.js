/*
 * Bibliotheque d'images de l'editeur des pages (App\Livewire\Espace\Pages).
 *
 * Le Redactor 3.5.2 recupere du legacy ne livre pas de vrai gestionnaire
 * d'images malgre le nom du plugin cite dans sa config d'origine
 * (ub_usadmin_core.js) : ni fichier plugin correspondant, ni UI de liste
 * dans le coeur. Ce plugin construit celle-ci, dans le style et selon les
 * conventions des plugins Redactor livres (voir video.min.js) :
 *   - le bouton d'insertion d'image de la barre ouvre la bibliotheque ;
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
                    add: 'Glisser-déposer vos images ici',
                    sending: 'Envoi en cours…',
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

        // Le lang.get de ce Redactor ignore les traductions des plugins
        // (chaine vide) : repli sur les libelles francais ci-dessus.
        t(cle) {
            return this.lang.get('imagemanager.' + cle) || this.translations.fr.imagemanager[cle] || cle;
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

            // Le bouton « image » de la barre ouvre la bibliotheque (depot,
            // choix, remplacement) plutot que la fenetre d'upload du coeur.
            const bouton = this.toolbar.getButton('image');
            if (bouton) bouton.setApi('plugin.imagemanager.open');
        },

        open() {
            this.app.api('module.modal.build', {
                title: this.t('library-title'),
                width: '620px',
                name: 'imagemanager',
                commands: { close: { title: this.t('close') } },
            });
        },

        onmodal: {
            imagemanager: {
                opened: function (e, modal) {
                    // Le second argument de ce rappel arrive vide avec ce
                    // Redactor : la fenetre ouverte est lue dans le DOM.
                    const liste = document.querySelector('.redactor-modal.open .redactor-imagemanager-liste');
                    this.charger(liste);
                },
            },
        },

        // Recharge la liste (apres ajout, remplacement ou suppression) sans
        // fermer la fenetre.
        async charger(conteneur) {
            conteneur.innerHTML = '<p class="redactor-imagemanager-message">' + this.t('loading') + '</p>';

            try {
                const reponse = await fetch(this.opts.imagemanagerListe, { headers: { Accept: 'application/json' } });
                const images = await reponse.json();
                this.afficher(conteneur, images);
            } catch (erreur) {
                conteneur.innerHTML = '<p class="redactor-imagemanager-message">' + this.t('error') + '</p>';
            }
        },

        afficher(conteneur, images) {
            conteneur.innerHTML = '';

            // Zone de depot : meme apparence que celle de /espace/galeries.
            const ajout = document.createElement('label');
            ajout.className = 'redactor-imagemanager-ajouter';
            ajout.innerHTML = '<span class="redactor-imagemanager-ajouter-icone">'
                + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">'
                + '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15V4m0 0L8 8m4-4 4 4M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/></svg></span>'
                + '<span class="redactor-imagemanager-ajouter-texte">' + this.t('add') + '</span>'
                + '<input type="file" multiple accept="image/jpeg,image/png,image/gif,image/webp" hidden>';
            const deposer = (fichiers) => {
                if (fichiers.length) {
                    ajout.querySelector('.redactor-imagemanager-ajouter-texte').textContent = this.t('sending');
                    this.envoyer([...fichiers], conteneur);
                }
            };
            ajout.querySelector('input').addEventListener('change', (e) => deposer(e.target.files));
            ajout.addEventListener('dragover', (e) => {
                if (e.dataTransfer.types.includes('Files')) {
                    e.preventDefault();
                    ajout.classList.add('survol');
                }
            });
            ajout.addEventListener('dragleave', () => ajout.classList.remove('survol'));
            ajout.addEventListener('drop', (e) => {
                e.preventDefault();
                ajout.classList.remove('survol');
                deposer(e.dataTransfer.files);
            });
            conteneur.appendChild(ajout);

            if (!images.length) {
                const vide = document.createElement('p');
                vide.className = 'redactor-imagemanager-message';
                vide.textContent = this.t('empty');
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
            remplacer.title = this.t('replace');
            remplacer.innerHTML = '↻<input type="file" accept="image/jpeg,image/png,image/gif,image/webp" hidden>';
            remplacer.querySelector('input').addEventListener('change', (e) => {
                if (e.target.files[0]) {
                    this.remplacer(image, e.target.files[0], conteneur);
                }
            });

            const supprimer = document.createElement('button');
            supprimer.type = 'button';
            supprimer.className = 'redactor-imagemanager-action';
            supprimer.title = this.t('delete');
            supprimer.textContent = '✕';
            supprimer.addEventListener('click', () => {
                if (window.confirm(this.t('confirm-delete'))) {
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

        async envoyer(fichiers, conteneur) {
            for (const fichier of fichiers) {
                const donnees = new FormData();
                donnees.append('file', fichier);
                Object.entries(this.opts.uploadData || {}).forEach(([cle, valeur]) => donnees.append(cle, valeur));

                await fetch(this.opts.imageUpload, { method: 'POST', body: donnees, headers: { Accept: 'application/json' } });
            }
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
