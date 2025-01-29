@php
    if (!isset($class)) $class = "btn-primary";
@endphp
<button type="button" class="btn {{ $class }}" data-toggle="modal" data-target="#{{$target}}">
  {!! $content !!}
</button>
