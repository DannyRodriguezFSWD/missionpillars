@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('communications.create') !!}
@endsection
@section('title')
    Create Communication
@endsection

@section('content')
<div id="selectEditorContainer">
    @include('communications.includes.selecteditor')
</div>

@include('communications.includes.templatepreviewmodal')

@include('communications.includes.templatelistmodal')

<div id="selectTemplateContainer" class="d-none">
    @include('communications.includes.selecttemplate')
</div>

@include('communications.includes.scripts.templatescripts')

<div id="communicationFormContainer" class="d-none">
    <div class="card">
        <div class="card-header">
            <a href="javascript:void(0)" class="pull-left" id="backToSelectTemplate">
                <span class="fa fa-chevron-left"></span>
                @lang('Back')
            </a>
        </div>
        
        <div class="card-body">
            {{ Form::open(['route' => ['communications.store'], 'id' => 'form', 'files' => true, 'autocomplete' => 'off']) }}
            @include('communications.includes.form', ['method' => 'Create'])
            {{ Form::close() }}
        </div>
        
        <div class="card-footer">&nbsp;</div>
    </div>
</div>

@include('communications.includes.sendtestemail')
@include('communications.includes.downloadtestpdf')
@include('includes.overlay')
@endsection
