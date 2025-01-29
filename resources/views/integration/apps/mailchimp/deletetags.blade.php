@extends('layouts.app')
@section('content')

@include('tags.includes.functions')
@push('scripts')
<script src="{{ asset('js/tags/events.js')}}"></script>
@endpush
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush

<div class="card">
    <div class="card-header">
        @lang('Export tags to Mailchimp List: ')
        {{ $listName }}
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-4">
                <ol class="tree">
                    <?php printFoldersTreeToDelete($tree, $root->id, $id, $list, $listName); ?>
                </ol>
            </div>
            <div class="col-sm-8">
                <div class="row">
                    <div class="col-sm-12">
                        <h6>{{ $root->name }}</h6>
                        <p>&nbsp;</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <table class="table table-striped" id="items">
                            @foreach($folders as $folder)
                            <tr>
                                <td>
                                    <a href="{{ route('mailchimp.deletetags', ['id' => $id, 'list' => $list, 'folder' => $folder->id, 'listname' => $listName]) }}">
                                        <span class="icon icon-folder-alt"></span> {{ $folder->name}}
                                    </a>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            @endforeach

                            @foreach($tags as $tag)
                            <tr>
                                <td>
                                    <span class="icon icon-tag"></span> {{ $tag->name}}
                                </td>
                                <td>
                                    <button type="button" class="btn btn-link text-danger" data-name="{{$tag->name}}" 
                                            data-href="{{ route('mailchimp.unsubscribetag', ['id' => $id, 'list' => $list, 'tagId' => Crypt::encrypt($tag->id), 'listname' => $listName]) }}"
                                            data-toggle="modal" data-target="#delete-modal">
                                        <span class="fa fa-trash"></span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-warning" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Mailchimp')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <p>@lang('Are you sure you want to unsubscribe all members under this tag from mailchimp list?')</p>
                    <small>@lang("This action can't be undone")</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                <button type="submit" class="btn btn-warning" id="button-delete">@lang('Unsubscribe All')</button>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script type="text/javascript">
    var url;

    $(function () {
        $('#delete-modal').on('shown.coreui.modal', function (e) {
            var button = $(e.relatedTarget);
            url = button.data('href');
        });

        $('#button-delete').on('click', function (e) {
            window.location.href = url;
        });
    });
</script>
@endpush

@endsection
