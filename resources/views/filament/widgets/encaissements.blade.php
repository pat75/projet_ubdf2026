{{--
 | Les quatre encaissements, en face du graphique du chiffre d'affaires.
 |
 | Pave de quatre cases, deux par deux : quatre lignes a plat laissaient
 | les montants serres a droite d'une carte vide ; en pave, chaque
 | montant respire et la carte remplit la hauteur du graphique.
 |
 | Le montant est la seule chose qui se lit de loin : il reste noir,
 | grand, en chiffres a chasse fixe pour que les quatre s'alignent. La
 | couleur ne sert qu'a la pastille de variation — une pastille de
 | quelques millimetres, pas quatre montants peints.
 |
 | Mise en forme dans `filament/styles.blade.php`, et non en classes
 | Tailwind : le panneau sert la feuille compilee de Filament, qui ne
 | contient que les classes de Filament. Ni dans un <style> de ce
 | gabarit : le bloc se charge en differe, et Livewire ne garde que
 | l'element racine du composant — une feuille posee a cote est jetee.
--}}
<x-filament-widgets::widget class="ub-encaissements">
    <x-filament::section :heading="__('Encaissé')">
        <div class="ub-encaissements-grille">
            @foreach ($this->lignes() as $ligne)
                <div class="ub-encaissement">
                    <div class="ub-encaissement-tete">
                        <span class="ub-encaissement-libelle">{{ $ligne['libelle'] }}</span>

                        @if ($ligne['variation'] !== null)
                            <span @class([
                                'ub-encaissement-variation',
                                'ub-hausse' => $ligne['en_hausse'],
                                'ub-baisse' => ! $ligne['en_hausse'],
                            ])>
                                <x-filament::icon
                                    :icon="$ligne['en_hausse'] ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'"
                                />
                                {{ $ligne['variation'] }}
                            </span>
                        @endif
                    </div>

                    <div>
                        <div class="ub-encaissement-montant">{{ $ligne['montant'] }}</div>
                        <div class="ub-encaissement-rappel">
                            {{ __(':montant en :annee', ['montant' => $ligne['precedent'], 'annee' => $ligne['annee']]) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
