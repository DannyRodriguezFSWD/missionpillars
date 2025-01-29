@php
    if(!isset($suppress_folder_name)) $suppress_folder_name = false;
@endphp
@foreach ($tags->sortBy('name') as $tag)
    @php
    // Pass either the tag name or the folder\tag name to cssDynamicFontSize
    // $css = cssDynamicFontSize(($suppress_folder_name || !$tag->folder->parent_folder_id ? '' : $tag->folder->name . '\\').$tag->name); 
    $css = '';
    @endphp
    <option value="{{$tag->id}}" class="tags_option" style="{{ $css }}">@include('tags.model-views.tag-string',compact('suppress_folder_name'))</option>
@endforeach
