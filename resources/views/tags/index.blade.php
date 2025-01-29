@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('tags.index') !!}
@endsection
@section('content')

@include('tags.includes.functions')

@push('scripts')
<script src="{{ asset('js/tags/events.js')}}"></script>
@endpush
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush


@if ($errors->has('tag'))
    <div class="alert alert-danger">
        {{ $errors->first('tag') }}
    </div>
@endif
@if ($errors->has('folder'))
    <div class="alert alert-danger">
        {{ $errors->first('folder') }}
    </div>
@endif

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    @if(Route::currentRouteName() === 'tags.show')
                        @include('widgets.back')
                    @else
                        <strong>Tags</strong>
                    @endif
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-sm-4">
                            <ol class="tree">
                                <?php printFoldersTree($tree); ?>
                            </ol>
                        </div>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h3>{{ $root->name }}</h3>
                                </div>
                                <div class="col-sm-6 text-right">
                                    @can('create',\App\Models\Folder::class)
                                        <button class="btn btn-success" data-toggle="modal" data-target="#folderModal">
                                            <span class="icon icon-folder-alt"></span>
                                            New Folder
                                        </button>
                                    @endcan
                                    @can('create',\App\Models\Tag::class)
                                        <button class="btn btn-success" data-toggle="modal" id="button-tag-modal" data-target="#tagModal">
                                            <span class="icon icon-tag"></span>
                                            New Tag
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped" id="items">
                                        @foreach($folders as $folder)
                                        <tr>
                                            <td>
                                                <a href="{{ route('tags.show', ['id' => $folder->id]) }}">
                                                    <span class="icon icon-folder-alt"></span> {{ $folder->name}}
                                                </a>
                                            </td>
                                            <td class="text-right">
                                                @can('update',$folder)
                                                    @include('folders.includes.edit-folder')
                                                @endcan
                                            </td>

                                            <td class="text-right">
                                                @can('delete',$folder)
                                                    @include('folders.includes.delete-folder')
                                                @endcan
                                            </td>
                                        </tr>
                                        @endforeach

                                        @foreach($tags as $tag)
                                        <tr>
                                            <td>
                                                <a href="{{ route('tags.contacts', ['id' => $tag->id]) }}">
                                                    @if(array_get($root, 'name') === 'Emails')
                                                    <span class="icon icon-tag"></span> {{ title_case($tag->name) }}
                                                    @else
                                                    <span class="icon icon-tag"></span> {{ $tag->name}}
                                                    @endif
                                                </a>
                                            </td>
                                            <td class="text-right">
                                                @can('update',$tag)
                                                    @include('tags.includes.edit-tag')
                                                @endcan
                                            </td>
                                            <td class="text-right">
                                                @can('delete',$tag)
                                                    @include('tags.includes.delete-tag')
                                                @endcan
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
        </div>
        <!--/.col-->
    </div>
    <!--/.row-->

@include('folders.includes.folders')
@include('tags.includes.tags')
@include('tags.includes.error')
@endsection
