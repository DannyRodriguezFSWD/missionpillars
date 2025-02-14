@if(!$tag->is_system_autogenerated)
{{ Form::model($tag, ['route' => ['tags.update', $tag->id], 'method' => 'put', 'id'=>'update-form-'.$tag->id]) }}
<button type="button" class="btn btn-link edit-tag" data-name="{{ $tag->name }}" data-form="#update-form-{{$tag->id}}" data-toggle="modal" data-target="#edit-tag-modal">
    <span class="fa fa-edit"></span>
</button>
{{ Form::hidden('uid',  Crypt::encrypt($tag->id)) }}
{{ Form::hidden('name', $tag->name) }}
{{ Form::hidden('folder_id', $root->id) }}
{{ Form::close() }}
@endif