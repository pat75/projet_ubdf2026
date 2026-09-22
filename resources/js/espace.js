import Sortable from 'sortablejs';

/*
 * x-espace-tri="methode" : rend les enfants [data-id] deplacables et envoie
 * le nouvel ordre a la methode Livewire du composant.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.directive('espace-tri', (el, { expression }) => {
        Sortable.create(el, {
            handle: el.querySelector('[data-poignee]') ? '[data-poignee]' : undefined,
            animation: 150,
            onEnd() {
                const ids = [...el.querySelectorAll(':scope > [data-id]')].map((n) => n.dataset.id);
                const composant = window.Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id'));
                composant.call(expression, ids);
            },
        });
    });
});
