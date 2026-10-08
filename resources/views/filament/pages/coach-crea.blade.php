@php
    $avatar = function ($u) {
        $url = $u->thumbnailUrl('front_desk');
        $initiales = mb_strtoupper(mb_substr($u->firstname ?: $u->login, 0, 1).mb_substr($u->lastname ?: '', 0, 1));

        return $url
            ? '<img src="'.e($url).'" alt="" class="h-10 w-10 shrink-0 rounded-full object-cover">'
            : '<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-400 text-sm font-bold text-white">'.e($initiales).'</span>';
    };
@endphp

<x-filament-panels::page>
    <x-filament::section :heading="'Messages à envoyer ('.$messages->count().')'">
        @forelse ($messages as $m)
            <div wire:key="msg-{{ $m->id }}" class="flex flex-col gap-3 border-b border-gray-200 py-4 last:border-b-0 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-3">
                    {!! $avatar($m->user) !!}
                    <div class="min-w-0 flex-1">
                        <div class="font-bold">{{ $m->user->fullName() }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Préparé le {{ $m->created_at->format('d/m/Y H:i') }}
                            · dernier message :
                            @if ($dernier = $derniersEnvois[$m->user_id] ?? null)
                                {{ \Illuminate\Support\Carbon::parse($dernier)->format('d/m/Y') }}
                                (il y a {{ trans_choice(':n jour|:n jours', $n = (int) \Illuminate\Support\Carbon::parse($dernier)->diffInDays(now()), ['n' => $n]) }})
                            @else
                                aucun
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.prise-identite.relais', ['creatif' => $m->user]) }}" target="_blank" class="text-sm text-primary-600 hover:underline">Se connecter à son compte</a>
                </div>

                <details class="text-sm text-gray-600 dark:text-gray-300">
                    <summary class="cursor-pointer">Diagnostic</summary>
                    <ul class="ml-5 mt-1 list-disc">
                        @foreach ($m->diagnostic as $conseil)<li>{{ $conseil }}</li>@endforeach
                    </ul>
                </details>

                <input type="text" wire:model="brouillons.{{ $m->id }}.objet" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                <textarea wire:model="brouillons.{{ $m->id }}.corps" rows="9" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"></textarea>
                @error("brouillons.{$m->id}.*")<div class="text-sm text-danger-600">{{ $message }}</div>@enderror

                <div class="flex gap-2">
                    <x-filament::button wire:click="envoyer({{ $m->id }})" icon="heroicon-o-paper-airplane">Envoyer</x-filament::button>
                    <x-filament::button wire:click="ignorer({{ $m->id }})" wire:confirm="Écarter ce message ?" color="gray">Ignorer</x-filament::button>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">Aucun message en attente.</p>
        @endforelse
    </x-filament::section>

    <x-filament::section :heading="'Dernières opérations ('.\App\Filament\Pages\CoachCrea::JOURS.' jours)'">
        @forelse ($activite as $operations)
            @php($u = $operations->first()->user)
            <details wire:key="act-{{ $u->id }}" class="border-b border-gray-200 py-3 last:border-b-0 dark:border-gray-700">
                <summary class="flex cursor-pointer list-none items-center gap-3">
                    {!! $avatar($u) !!}
                    <div class="min-w-0 flex-1">
                        <div class="font-bold">{{ $u->fullName() }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ trans_choice(':n opération|:n opérations', $operations->count(), ['n' => $operations->count()]) }}
                            · dernière le {{ $operations->first()->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <a href="{{ route('admin.prise-identite.relais', ['creatif' => $u]) }}" target="_blank" class="text-sm text-primary-600 hover:underline">Se connecter à son compte</a>
                </summary>

                @foreach ($operations->groupBy(fn ($o) => $o->created_at->format('d/m/Y')) as $jour => $duJour)
                    <div class="ml-13 mt-3 text-sm">
                        <div class="font-semibold">{{ $jour }}</div>
                        <ul class="mt-1 space-y-0.5">
                            @foreach ($duJour as $o)
                                <li><span class="text-gray-500">{{ $o->created_at->format('H:i') }}</span> {{ $o->resume() }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </details>
        @empty
            <p class="text-sm text-gray-500">Aucune opération sur les {{ \App\Filament\Pages\CoachCrea::JOURS }} derniers jours.</p>
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
