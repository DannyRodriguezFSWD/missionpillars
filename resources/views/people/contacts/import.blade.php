@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('import_contacts') !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <ol>
            <li>
                @lang('Download contact template spreadsheet ')
                <a href="{{ asset('import-contacts-template.csv') }}">
                    @lang('here')
                    <span class="fa fa-download"></span>
                </a>
            </li>
            <li>@lang('Fill spreadsheet with contacts')</li>
            <li>@lang('Click browse to select your spreadsheet')</li>
            <li>@lang('Submit your spreadsheet')</li>
        </ol>
        
        <i>NOTE: For the best results, if you are using an export from a different system, please ensure that the headings match the headings in the template where possible</i>
    </div>
    <div class="card-body">
        {{ Form::open(['route' => 'contacts.upload.data.sheet', 'files' => true]) }}
        <div class="form-group">
            {{ Form::file('file', ['required' => true, 'accept' => '.csv']) }}
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <span class="fa fa-upload"></span>
                @lang('Submit')
            </button>
        </div>
        {{ Form::close() }}
    </div>
    <div class="card-footer">&nbsp;</div>
</div>

@push('scripts')
    <script>
        $('button[type="submit"]').on('click', function(e){
            $('#overlay').show();
        });
    </script>
@endpush

@endsection
