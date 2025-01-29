@php
    $sortedparentpurposes = $parentpurposes->sortBy('name')
    ->sortBy(function ($p) { return $p->parent_purposes_id != null; });
@endphp
@foreach ($sortedparentpurposes as $purpose)
    @php
        // $css = cssDynamicFontSize($purpose->name);
        $css = '';
    @endphp
    @include('purposes.model-views.option',compact('purpose'));
    @if ($purpose->childPurposes->count())
        <optgroup label="{{$purpose->name}}" style="{{$css}}">
            @include('purposes.model-views.childpurposes-options',['purposes'=>$purpose->childPurposes]);
        </optgroup>
    @endif
@endforeach
