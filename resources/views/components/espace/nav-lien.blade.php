@props(['route'])
{{-- Les rubriques pas encore livrees n'apparaissent pas : pas de lien mort. --}}
@if (Route::has($route))
    @php($actif = request()->routeIs($route) || request()->routeIs($route.'.*'))
    <a href="{{ route($route) }}"
       @class([
           'whitespace-nowrap rounded-md px-3 py-2 text-sm',
           'bg-gray-100 font-medium dark:bg-gray-700' => $actif,
           'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' => ! $actif,
       ])
       @if ($actif) aria-current="page" @endif>{{ $slot }}</a>
@endif
