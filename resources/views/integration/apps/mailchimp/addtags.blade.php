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
                    <?php printFoldersTreeToExport($tree, $root->id, $id, $list, $listName); ?>
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
                                    <a href="{{ route('mailchimp.addtags', ['id' => $id, 'list' => $list, 'folder' => $folder->id, 'listname' => $listName]) }}">
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
                                    {{ Form::open(['route' => ['mailchimp.store', $id, $list, 'listname='.$listName]]) }}
                                    {{ Form::hidden('tid', Crypt::encrypt($tag->id)) }}
                                    <button class="btn btn-link" type="submit"><i class="icon icon-paper-plane"></i></button>
                                    {{ Form::close() }}
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

@endsection
