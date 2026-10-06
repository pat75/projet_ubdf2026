{{--
 | Ouverture en serie des books de la page courante, un par onglet.
 |
 | Le serveur ne peut pas ouvrir d'onglets : il envoie la liste des
 | adresses, le navigateur les ouvre. Sans autorisation des fenetres
 | surgissantes pour ce site, le navigateur n'accepte qu'un onglet par
 | clic : `window.open` rend null pour les suivants. Les adresses refusees
 | sont alors gardees dans un bandeau, qui explique comment tout autoriser
 | et ouvre le reste a la demande (un clic = un geste = un onglet de plus,
 | ou tous d'un coup une fois les fenetres autorisees).
--}}
<script>
    (function () {
        var restantes = [];

        function bandeau() {
            var b = document.getElementById('ub-books-bloques');

            if (! restantes.length) {
                if (b) b.remove();
                return;
            }

            if (! b) {
                b = document.createElement('div');
                b.id = 'ub-books-bloques';
                b.style.cssText = 'position:fixed;right:24px;bottom:24px;z-index:9999;max-width:380px;padding:16px 18px;background:#383e42;color:#fff;font-size:14px;line-height:20px;box-shadow:0 10px 30px rgba(0,0,0,.3)';
                document.body.appendChild(b);
            }

            b.innerHTML = '<strong style="display:block;margin-bottom:6px">' + restantes.length + ' book(s) bloqué(s) par le navigateur</strong>'
                + '<span style="display:block;margin-bottom:12px;color:#d1d5db">Autorisez les fenêtres surgissantes pour ce site (icône à droite de la barre d’adresse), puis relancez — ou ouvrez-les ici.</span>'
                + '<button type="button" data-action="ouvrir" style="background:#fff;color:#111;font-weight:700;padding:6px 12px;margin-right:8px">Ouvrir les ' + restantes.length + ' restants</button>'
                + '<button type="button" data-action="fermer" style="color:#d1d5db;padding:6px 4px">Fermer</button>';

            b.querySelector('[data-action=ouvrir]').onclick = function () { ouvrir(restantes.splice(0)); };
            b.querySelector('[data-action=fermer]').onclick = function () { restantes = []; bandeau(); };
        }

        function ouvrir(urls) {
            var refusees = [];

            urls.forEach(function (url) {
                if (! window.open(url, '_blank')) {
                    refusees.push(url);
                }
            });

            restantes = refusees.concat(restantes);
            bandeau();
        }

        window.addEventListener('ouvrir-books', function (e) {
            ouvrir((e.detail && e.detail.urls) || []);
        });
    })();
</script>
