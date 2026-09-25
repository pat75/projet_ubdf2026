/*
 * Controles de formulaire Semantic UI (liste deroulante, case a cocher)
 * animes par Alpine sur leur HTML d'origine, a la place des modules
 * dropdown et checkbox de Semantic. Les classes posees (active, visible,
 * selected, checked) sont celles qu'attendent les feuilles du front 2018.
 */
export default function controles(Alpine) {
    /*
     * <div class="ui selection dropdown" x-data="listeDeroulante"> avec, a
     * l'interieur, un <input type="hidden">, un .text et un .menu d'.item
     * portant data-value. Le choix met a jour le champ cache et emet
     * l'evenement `choix` (detail : la valeur).
     */
    Alpine.data('listeDeroulante', () => ({
        ouverte: false,

        init() {
            this.$el.setAttribute('tabindex', '0');
            this.$watch('ouverte', (ouverte) => {
                this.$el.classList.toggle('active', ouverte);
                this.$el.classList.toggle('visible', ouverte);
                const menu = this.$el.querySelector('.menu');
                menu.classList.toggle('visible', ouverte);
                menu.classList.toggle('transition', ouverte);
                menu.style.display = ouverte ? 'block' : '';
            });

            const initiale = this.$el.querySelector('input[type=hidden]')?.value;
            if (initiale) {
                this.choisir(this.$el.querySelector(`.item[data-value="${CSS.escape(initiale)}"]`), false);
            }
        },

        // Bindings poses sur la racine : ouverture, fermeture, choix.
        racine: {
            ['@click'](e) {
                const item = e.target.closest('.item');
                if (item) {
                    this.choisir(item);
                    this.ouverte = false;
                } else {
                    this.ouverte = !this.ouverte;
                }
            },
            ['@click.outside']() {
                this.ouverte = false;
            },
            ['@keydown.escape.stop']() {
                this.ouverte = false;
            },
        },

        choisir(item, emettre = true) {
            if (!item) return;
            const champ = this.$el.querySelector('input[type=hidden]');
            champ.value = item.dataset.value;
            const texte = this.$el.querySelector('.text');
            texte.innerHTML = item.innerHTML;
            texte.classList.remove('default');
            this.$el.querySelectorAll('.item').forEach((i) => i.classList.toggle('active', i === item));
            this.$el.querySelectorAll('.item').forEach((i) => i.classList.toggle('selected', i === item));
            if (emettre) this.$dispatch('choix', item.dataset.value);
        },
    }));

    /*
     * <div class="ui checkbox" x-data="caseACocher"> : le champ est masque
     * par Semantic (classe hidden), un clic sur la case ou son libelle le
     * bascule. Un lien dans le libelle garde son comportement.
     */
    Alpine.data('caseACocher', () => ({
        racine: {
            ['@click'](e) {
                if (e.target.closest('a')) return;
                const champ = this.$el.querySelector('input[type=checkbox]');
                // Clic direct sur le champ (visible dans certaines cases) :
                // le navigateur l'a deja bascule, seul l'aspect suit.
                if (e.target === champ) {
                    this.$el.classList.toggle('checked', champ.checked);
                    return;
                }
                champ.checked = !champ.checked;
                this.$el.classList.toggle('checked', champ.checked);
                champ.dispatchEvent(new Event('change', { bubbles: true }));
            },
        },
    }));
}
