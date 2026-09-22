import Sortable from 'sortablejs';
import 'trix';
import 'trix/dist/trix.css';

// L'editeur n'accepte pas de fichiers joints : les images passent par les galeries.
document.addEventListener('trix-file-accept', (e) => e.preventDefault());

/*
 * Rend les enfants [data-id] de `el` deplacables ; `envoyer` recoit le
 * nouvel ordre des identifiants.
 */
window.espaceTri = (el, envoyer) => Sortable.create(el, {
    handle: el.querySelector('[data-poignee]') ? '[data-poignee]' : undefined,
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
});
