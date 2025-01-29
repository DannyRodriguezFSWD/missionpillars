@include('people.contacts.includes.functions')
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush

<div class="row">
    <div class="col-md-12">
        <p class="lead bg-faded">@lang('Tags')</p>
    </div>
</div>

<div class="row">
    <div class="col-md-9">
        @foreach($contact->tags as $tag)
        <a href="{{ route('tags.contacts', ['id' => $tag->id]) }}" class="p-1" style="padding-bottom: 5px; float: left;">
            <span class="badge badge-pill badge-success p-2">
                <i class="icon icon-tag"></i> {{ $tag->name }}
            </span>
        </a>
        @endforeach
    </div>
    <div class="col-md-3 text-right pb-2">
        <div class="" id="floating-buttons">
            <button id="btn-submit-contact" type="submit" class="btn btn-primary"><i class="icons icon-note"></i> Save</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <ol class="tree">
            <?php printFoldersTree($tree, $tagsArray); ?>
        </ol>
    </div>
    
</div>
