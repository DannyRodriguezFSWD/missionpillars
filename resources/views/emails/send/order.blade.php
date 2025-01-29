@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => $link])
            {{ $contact->organization }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {!! $contact->content.'<br>'.__('Thanks').'<br>'.$contact->organization !!}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ $contact->organization }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent