@php
    if(!isset($suppress_folder_name)) $suppress_folder_name = false;
@endphp
@if (!$suppress_folder_name && $tag->folder->folder_parent_id)
    {{ $tag->folder->name }}\{{ $tag->name }}
@else
    {{ $tag->name }}
@endif
