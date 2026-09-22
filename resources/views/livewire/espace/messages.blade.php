<div>
    <h1 class="text-2xl font-light">{{ __('Messages') }}</h1>

    <div class="mt-6 gap-6 md:grid md:grid-cols-5">
        <ul class="divide-y divide-gray-200 rounded-lg bg-white shadow-sm md:col-span-2 dark:divide-gray-600 dark:bg-gray-800">
            @forelse ($conversations as $c)
                <li wire:key="conv-{{ $c->id }}">
                    <button type="button" wire:click="ouvrir({{ $c->id }})"
                            @class(['block w-full px-4 py-3 text-left', 'bg-gray-100 dark:bg-gray-700' => $ouvert === $c->id])>
                        <span class="flex items-center justify-between gap-2">
                            <span @class(['truncate text-sm', 'font-semibold' => $c->non_lus])>{{ $c->sender_name ?: $c->sender_email }}</span>
                            <span class="whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $c->last_message_at?->format('d/m/Y') }}</span>
                        </span>
                        <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ $c->objet() }}</span>
                    </button>
                </li>
            @empty
                <li class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Aucun message pour le moment.') }}</li>
            @endforelse
        </ul>

        <section class="mt-6 md:col-span-3 md:mt-0">
            @if ($fil)
                <header class="mb-4">
                    <h2 class="text-lg font-light">{{ $fil->objet() }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $fil->sender_name }}@if ($fil->sender_company), {{ $fil->sender_company }}@endif
                        — <a href="mailto:{{ $fil->sender_email }}" class="underline">{{ $fil->sender_email }}</a>
                        @if ($fil->sender_phone) — {{ $fil->sender_phone }} @endif
                    </p>
                </header>

                <ol class="space-y-3">
                    @foreach ($fil->messages as $m)
                        <li @class(['rounded-lg px-4 py-3 text-sm whitespace-pre-line',
                                'ml-8 bg-gray-900 text-white dark:bg-gray-700' => $m->from_owner,
                                'mr-8 bg-white dark:bg-gray-800' => ! $m->from_owner])>{{ $m->body }}<span class="mt-1 block text-xs opacity-60">{{ $m->created_at?->format('d/m/Y H:i') }}</span></li>
                    @endforeach
                </ol>

                <form wire:submit="repondre" class="mt-4 space-y-2">
                    <textarea wire:model="reponse" rows="4" placeholder="{{ __('Votre réponse') }}"
                              class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"></textarea>
                    @error('reponse') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <x-espace.bouton wire:target="repondre" wire:loading.attr="disabled">{{ __('Envoyer') }}</x-espace.bouton>
                </form>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Choisissez un message.') }}</p>
            @endif
        </section>
    </div>

    <div class="mt-4">{{ $conversations->links() }}</div>
</div>
