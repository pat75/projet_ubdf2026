{{--
 | Ouverture en serie des books de la page courante, un par onglet.
 |
 | Le serveur ne peut pas ouvrir d'onglets : il envoie la liste des
 | adresses, le navigateur les ouvre. `window.open` rend null quand le
 | bloqueur de fenetres s'interpose — on compte les refus et on le dit,
 | plutot que de laisser l'administrateur devant un seul onglet ouvert
 | en se demandant pourquoi.
--}}
<script>
    window.addEventListener('ouvrir-books', function (e) {
        var urls = (e.detail && e.detail.urls) || [];
        var bloques = 0;

        urls.forEach(function (url) {
            var onglet = window.open(url, '_blank');

            if (! onglet) {
                bloques++;
            }
        });

        if (bloques > 0) {
            window.dispatchEvent(new CustomEvent('books-bloques', { detail: { n: bloques } }));
        }
    });
</script>
