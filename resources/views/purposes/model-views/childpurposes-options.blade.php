@php
    $sortedchildpurposes = $purposes->sortBy('name');
@endphp
@foreach ($sortedchildpurposes as $childpurpose)
    @include('purposes.model-views.option',['purpose'=>$childpurpose]);
@endforeach
