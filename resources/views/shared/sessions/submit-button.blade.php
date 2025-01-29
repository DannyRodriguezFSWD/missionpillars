@php
$button = [
    'size' => isset($size) ? $size : 'btn-lg',
    'background' => isset($background) ? $background : 'btn-success',
    'textcolor' => isset($textcolor) ? $textcolor : 'text-white',
    'not-flat' => isset($notFlat)
];
@endphp

@if($form)
{{ Form::open(['route' => 'redirect.store']) }}
@endif

{{ Form::hidden('start_url', $start_url) }}
{{ Form::hidden('next_url', $next_url) }}
<button type="submit" class="btn {{ array_get($button, 'size') }} {{ array_get($button, 'background') }} {{ array_get($button, 'text-white') }}" @if(!array_get($button,'not-flat',false)) style="border-radius: 0;" @endif>
    {!! $caption !!}
</button>
@if($form)
{{ Form::close() }}
@endif

