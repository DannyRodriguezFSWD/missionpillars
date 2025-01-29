@php
    // $css = cssDynamicFontSize($purpose->name);
    $css = '';
@endphp
<option value="{{$purpose->id}}" class="purpose_option" style="{{ $css }}">{{ $purpose->name }}</option>
