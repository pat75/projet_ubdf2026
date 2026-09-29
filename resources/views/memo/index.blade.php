{{-- Memo book : meme page pour un creatif connecte (dans son espace) et
     pour un visiteur (layout visiteur). --}}
@extends(auth('web')->check() ? 'layouts.espace' : 'layouts.visiteur')

@section('title', __('mémoBook'))

@section('content')
    <livewire:memo.liste />
@endsection
