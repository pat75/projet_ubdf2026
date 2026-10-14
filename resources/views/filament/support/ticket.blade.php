@php
    $ticket = $this->record;
    $user = $ticket->user;
@endphp

<x-filament-panels::page>
    <x-filament::section>
        <div style="display:flex;flex-wrap:wrap;gap:8px 24px;font-size:14px">
            @if ($ticket->compte_requis)
                <x-filament::badge color="danger" icon="heroicon-o-exclamation-triangle" tooltip="Demande envoyée sans connexion avec l’adresse d’un compte : ne communiquer aucune information sur le compte.">Identité non vérifiée</x-filament::badge>
            @endif
            <span><strong>Adresse :</strong> {{ $ticket->adresse() }}</span>
            <span><strong>IP :</strong> {{ $ticket->ip ?? '—' }}</span>
            @if ($user)
                <span><strong>Formule :</strong> {{ \App\Filament\Resources\SupportTickets\SupportTicketResource::estPayant($ticket) ? 'payante, jusqu’au '.$user->echeanceFormule()->format('d/m/Y') : 'gratuite' }}</span>
            @endif
            <span><strong>Statut :</strong> {{ \App\Models\SupportTicket::STATUTS[$ticket->statut] ?? $ticket->statut }}{{ $ticket->is_spam ? ' — spam' : '' }}</span>
            @if ($ticket->page_origine)
                <span><strong>Depuis :</strong> {{ \Illuminate\Support\Str::limit($ticket->page_origine, 80) }}</span>
            @endif
            <span style="opacity:.6">{{ \Illuminate\Support\Str::limit((string) $ticket->navigateur, 90) }}</span>
        </div>
    </x-filament::section>

    <div style="display:flex;flex-direction:column;gap:14px">
        @foreach ($ticket->messages as $msg)
            @php($support = $msg->auteur === 'admin')
            <div style="{{ $support ? 'margin-left:60px' : 'margin-right:60px' }}">
                <div style="padding:12px 14px;white-space:pre-wrap;border-radius:4px;background:{{ $support ? 'rgba(59,130,246,.12)' : 'rgba(120,120,120,.12)' }}">{{ $msg->corps }}@include('front.partials.support-piece', ['msg' => $msg])</div>
                <div style="font-size:12px;opacity:.7;margin-top:4px">
                    <strong>{{ $support ? ($msg->admin?->name ?? 'Support') : ($user?->login ?? $ticket->email) }}</strong>
                    — {{ $msg->created_at->timezone('Europe/Paris')->format('d/m/Y H:i') }}
                </div>
            </div>
        @endforeach
    </div>

    <x-filament::section heading="Répondre">
        <form wire:submit="envoyer" style="display:flex;flex-direction:column;gap:12px">
            <x-filament::input.wrapper :valid="! $errors->has('reponse')">
                <textarea wire:model="reponse" rows="8" class="fi-input" style="width:100%;resize:vertical" placeholder="Votre réponse…"></textarea>
            </x-filament::input.wrapper>
            @error('reponse')<div style="color:#dc2626;font-size:13px">{{ $message }}</div>@enderror

            <div style="display:flex;flex-wrap:wrap;gap:8px">
                <x-filament::button type="submit" icon="heroicon-o-paper-airplane" wire:target="envoyer">Envoyer</x-filament::button>
                <x-filament::button type="button" color="gray" icon="heroicon-o-sparkles" wire:click="corriger" wire:target="corriger">Corriger l’orthographe</x-filament::button>
                <x-filament::button type="button" color="gray" icon="heroicon-o-pencil" wire:click="signer('patrice')" wire:target="signer">Signature Patrice</x-filament::button>
                <x-filament::button type="button" color="gray" icon="heroicon-o-pencil" wire:click="signer('equipe')" wire:target="signer">Signature {{ \App\Support\Marque::depuisCode($this->record->brand ?: 'ub')->nom }}</x-filament::button>
                <x-filament::button type="button" color="gray" icon="heroicon-o-light-bulb" wire:click="suggerer" wire:target="suggerer">Suggérer une réponse</x-filament::button>
            </div>
            <p style="font-size:12px;opacity:.6">La suggestion s’appuie sur les fiches actives de la base de connaissances. Elle remplace le texte en cours.</p>
        </form>
    </x-filament::section>
</x-filament-panels::page>
