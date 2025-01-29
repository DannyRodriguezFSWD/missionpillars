@php
    $sortedfolders = $tagfolders->sortBy('name')
    ->sortBy(function ($f) { return $f->folder_parent_id != null; });
@endphp
@foreach ($sortedfolders as $folder)
    @php
        // $css = cssDynamicFontSize($folder->name);
        $css = '';
    @endphp
    <optgroup label="{{$folder->name}}" style="{{$css}}">
        @php
            $tags = $folder->tags->sortBy('name');
        @endphp
        @include('tags.model-views.options',['suppress_folder_name'=>true]);
    </optgroup>
@endforeach
