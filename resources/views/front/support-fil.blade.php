@extends('layouts.portail')

@section('title', __('Support et contact').' | '.$marque->nom)
@section('body_class', 'page_support')

@section('content')
    <div class="ui text container" style="padding:90px 0 140px">
        {{-- Marge en ligne : la regle h1 du portail (.67em, ~17px) l emporte sur Tailwind ; +30 % puis +20 %. --}}
        <h1 class="text-[26px]" style="margin-bottom:28px">{{ __('Support et contact') }}</h1>

        @if ($verrou ?? false)
            {{-- Demande liee a un compte : lecture apres connexion seulement.
                 La fenetre de connexion s'ouvre d'elle-meme (connexion_ouverte). --}}
            <div class="flex flex-col items-start gap-5 text-[17px] text-[#333] text-pretty">
                <p style="margin:0">
                    <span class="block">{{ __('Cette discussion est liée à un compte.') }}</span>
                    <span class="block">{{ __('Connectez-vous pour la lire.') }}</span>
                </p>
                <button type="button" @click="$store.modale.ouvrir('connexion')"
                        class="inline-flex cursor-pointer items-center gap-2 border-0 bg-[#111] px-8 py-3 text-[13px] uppercase tracking-[1.3px] text-white transition hover:opacity-80">
                    {{ __('Se connecter') }}
                </button>
                <p class="text-[15px] text-[#666] text-pretty" style="margin:0">
                    <span class="block">{{ __('Vous n’arrivez plus à vous connecter, malgré une demande de nouveau mot de passe ?') }}</span>
                    <span class="block">{!! __('Écrivez-nous à :email.', ['email' => '<a href="mailto:'.e($marque->email).'" class="text-[#111] underline">'.e($marque->email).'</a>']) !!}</span>
                </p>
            </div>
            {{-- Visiteur : la connexion le ramene ici (champ `retour`, ConnexionController). --}}
            <script>document.addEventListener('DOMContentLoaded', () => { const r = document.querySelector('#login_form [name="retour"]'); if (r) r.value = location.pathname + location.search; });</script>
        @elseif (session('envoye'))
            {{-- Juste apres l'envoi : confirmation, puis le message envoye
                 seul. Ni fil ni champ de reponse a ce stade. --}}
            @php($dernier = $ticket->messages->where('auteur', 'client')->last())
            <div class="mb-10 text-pretty">
                <p class="flex items-center gap-4 text-[40px] leading-tight text-green-700" style="margin:0">
                    <svg class="size-14 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                    {{ __('Votre message a été envoyé.') }}
                </p>
                {{-- Marges en ligne : Semantic UI (hors couche) l'emporte sur Tailwind pour les <p>.
                     72px = icone (56) + ecart (16) : aligne sur le « V » de la ligne du dessus. --}}
                <p class="pl-[72px] text-[17px] text-[#333]" style="margin:0">{{ __('Vous recevrez la réponse par e-mail sur :email.', ['email' => $ticket->adresse()]) }}</p>
                @if ($ticket->compte_requis)
                    <p class="pl-[72px] text-[17px] text-[#333]" style="margin:4px 0 0">{{ __('Cette adresse correspond à un compte : connectez-vous pour lire la réponse.') }}</p>
                @endif
            </div>

            {{-- Bulle blanche sans filet ni ombre, queue en bas a gauche pointant vers l'auteur et la date. --}}
            <div class="max-w-xl">
                <div class="relative rounded-[6px] bg-white px-9 py-3.5 text-[16px] font-light whitespace-pre-wrap">{{ $dernier?->corps }}@if ($dernier)@include('front.partials.support-piece', ['msg' => $dernier])@endif<span class="absolute -bottom-[7px] left-6 size-3 rotate-45 bg-white" aria-hidden="true"></span></div>
                <div class="mt-3 text-[12px] text-[#777]">
                    <span class="font-light text-black">{{ $ticket->adresse() }}</span>
                    — {{ $dernier?->created_at->translatedFormat('j F Y à H:i') }}
                </div>
            </div>
        @else
            {{-- Saisie (ou etat clos) en tete, au-dessus du fil. --}}
            <div class="mb-[62px]">
            @if ($ticket->statut !== 'clos')
                <div x-data="{ resolu: false, note: 0, survol: 0 }">
                    {{-- Meme URL signee que la page : la signature autorise l'envoi. --}}
                    <form x-show="! resolu" method="post" action="{{ request()->fullUrl() }}" enctype="multipart/form-data" novalidate class="flex flex-col gap-6">
                        @csrf
                        <div>
                            <textarea id="contact-message" name="message" rows="3" placeholder="{{ __('Ajouter un message') }}" aria-label="{{ __('Ajouter un message') }}"
                                      oninput="this.style.height = 'auto'; this.style.height = this.scrollHeight + 'px'"
                                      class="w-full resize-none border-0 border-b border-[#767676] bg-transparent px-0 py-2 text-[16px] font-light text-[#111] outline-none placeholder:text-[#666] focus:border-[#111] focus:ring-0">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
                        </div>
                        @if ($ticket->user_id && auth('web')->id() === $ticket->user_id)
                            {{-- Piece jointe : createur connecte uniquement, rangee dans son book. --}}
                            <div>
                                <label class="inline-flex cursor-pointer items-center gap-2 text-[14px] text-[#333] hover:text-[#111]">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m21 12-8.5 8.5a5 5 0 0 1-7-7L14 5a3.5 3.5 0 0 1 5 5l-8.5 8.5a2 2 0 0 1-3-3L15 8"/></svg>
                                    <span>{{ __('Joindre une image ou un PDF') }}</span>
                                    <input type="file" name="piece" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf" class="sr-only"
                                           onchange="this.nextElementSibling.textContent = this.files[0]?.name ?? ''">
                                    <span class="font-light text-[#666]"></span>
                                </label>
                                @error('piece')<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endif
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" onclick="this.disabled = true; this.form.submit()"
                                    class="inline-flex cursor-pointer items-center gap-2 border-0 bg-[#111] px-8 py-3 text-[13px] uppercase tracking-[1.3px] text-white transition hover:opacity-80 disabled:opacity-60">
                                {{ __('Envoyer') }}
                            </button>
                            <button type="button" @click="resolu = true"
                                    class="inline-flex cursor-pointer items-center gap-2 border border-[#111] bg-white px-8 py-3 text-[13px] uppercase tracking-[1.3px] text-[#111] transition hover:bg-[#f2f2f0]">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                                {{ __('Résolu') }}
                            </button>
                        </div>
                    </form>

                    {{-- Cloture : note facultative de 1 a 5 etoiles. --}}
                    <form x-show="resolu" x-cloak method="post" action="{{ $ticket->lienResolu() }}" class="bg-white px-6 py-5">
                        @csrf
                        <input type="hidden" name="note" :value="note || ''">
                        <p class="text-[20px] text-green-700" style="margin:0">{{ __('Ravis d’avoir pu vous aider.') }}</p>
                        <p class="text-[15px] text-[#333]" style="margin:4px 0 0">{{ __('Notez notre réponse, puis clôturez la discussion.') }}</p>
                        <div class="mt-3 flex gap-1" @mouseleave="survol = 0">
                            <template x-for="i in 5" :key="i">
                                <button type="button" @click="note = i" @mouseenter="survol = i" :aria-label="i + ' / 5'"
                                        class="cursor-pointer border-0 bg-transparent p-0 text-[34px] leading-none"
                                        :class="i <= (survol || note) ? 'text-[#f5a623]' : 'text-[#ccc]'">★</button>
                            </template>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex cursor-pointer items-center gap-2 border-0 bg-[#111] px-8 py-3 text-[13px] uppercase tracking-[1.3px] text-white transition hover:opacity-80">
                                {{ __('Clôturer la discussion') }}
                            </button>
                            <button type="button" @click="resolu = false" class="cursor-pointer border-0 bg-transparent text-[13px] text-[#666] underline">{{ __('Annuler') }}</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex flex-wrap items-center gap-x-4 gap-y-3 text-[15px] text-[#333]">
                    @include('front.partials.support-etoiles', ['note' => $ticket->note])
                    <span>{{ __('Cette discussion est close.') }}</span>
                </div>
                <a href="{{ lien('support') }}"
                   class="mt-6 inline-flex items-center gap-2 bg-[#111] px-8 py-3 text-[13px] uppercase tracking-[1.3px] text-white no-underline transition hover:text-white hover:opacity-80">
                    {{ __('Nous écrire à nouveau') }}
                </a>
            @endif
            </div>

            {{-- Du plus recent au plus ancien. Questions sur gris soutenu, reponses du support sur blanc. --}}
            @php($separe = false)
            @foreach ($ticket->messages->reverse() as $msg)
                {{-- Separateur a la cloture : les messages au-dessus l'ont suivie. --}}
                @if (! $separe && $ticket->clos_at && $msg->created_at <= $ticket->clos_at)
                    @php($separe = true)
                    <div class="my-8 flex items-center gap-4 text-[13px] uppercase tracking-[1.2px] text-[#666]">
                        <span class="h-px flex-1 bg-[#bbb]"></span>
                        <span>{{ __('Discussion close le :date', ['date' => $ticket->clos_at->translatedFormat('j F Y')]) }}</span>
                        @include('front.partials.support-etoiles', ['note' => $ticket->note, 'taille' => 16])
                        <span class="h-px flex-1 bg-[#bbb]"></span>
                    </div>
                @endif
                @php($support = $msg->auteur === 'admin')
                {{-- Bulle a la largeur du message, queue en bas a gauche vers l'expediteur. --}}
                <div class="mb-5 flex flex-col items-start {{ $support ? 'ml-10' : 'mr-10' }}">
                    <div class="relative max-w-full whitespace-pre-wrap rounded-[6px] px-9 py-3.5 text-[16px] font-light {{ $support ? 'border border-[#e3e3e0] bg-white' : 'bg-[#dcdcd8]' }}">{{ $msg->corps }}@include('front.partials.support-piece', ['msg' => $msg])<span class="absolute -bottom-[7px] left-6 size-3 rotate-45 {{ $support ? 'border-r border-b border-[#e3e3e0] bg-white' : 'bg-[#dcdcd8]' }}" aria-hidden="true"></span></div>
                    <div class="mt-3 text-[12px] text-[#777]">
                        <strong class="text-[#333]">{{ $support ? __('Support :marque', ['marque' => $marque->nom]) : ($ticket->user?->fullName() ?? $ticket->email) }}</strong>
                        — {{ $msg->created_at->translatedFormat('j F Y à H:i') }}
                    </div>
                </div>
            @endforeach

        @endif
    </div>
@endsection
