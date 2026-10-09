{{-- Dernieres analyses de visuels : quelle IA a repondu, et quand
     (App\Filament\Pages\ReglageNvidia::dernieresAnalyses). Styles : filament/styles. --}}
@if ($analyses->isEmpty())
    <p class="ub-ia-modele">Aucune analyse pour l’instant.</p>
@else
    <table class="ub-ia-table">
        <thead>
            <tr><th>Date et heure</th><th>IA</th><th>Modèle</th><th>Book</th></tr>
        </thead>
        <tbody>
            @foreach ($analyses as $analyse)
                <tr>
                    <td class="ub-ia-date">{{ $analyse['date'] }}</td>
                    <td class="{{ $analyse['nvidia'] ? 'ub-ia-nvidia' : 'ub-ia-openrouter' }}">{{ $analyse['nvidia'] ? 'NVIDIA' : 'OpenRouter' }}</td>
                    <td class="ub-ia-modele">{{ $analyse['modele'] }}</td>
                    <td>{{ $analyse['login'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
