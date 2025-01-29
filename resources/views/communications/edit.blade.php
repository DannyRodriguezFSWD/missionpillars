@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('communications.show',$communication) !!}
@endsection
@section('title')
    Edit Communication
@endsection

@section('content')

@if ($totalEmailsScheduled > 0) 
<div class="alert alert-warning">
    There are <b>{{ $totalEmailsScheduled }} emails</b> scheduled for 
    <b>{{ displayLocalDateTime(array_get($communication, 'time_scheduled'))->format('D, M j g:i A') }}</b>. 
    You will not be able to update or resend this communication until these email are sent.
</div>

<a href="{{ route('communications.emailsummary',$communication->id) }}" class="btn btn-primary">View Email Summary</a>
<button class="btn btn-danger" onclick="cancelSend('{{ route('communications.cancel-send', $communication->id) }}');">
    Cancel Send/Edit
</button>

@push('scripts')
<script>
    function cancelSend(url) {
        customAjax({
            url: url,
            success: function (response) {
                if (response.success) {
                    Swal.fire('Scheduled emails have been canceled', '', 'success');
                    window.location.reload();
                }
            }
        });
    }
</script>
@endpush

@else
<div id="selectEditorContainer" class="@if($communication->email_editor_type !== 'none') d-none @endif">
    @include('communications.includes.selecteditor')
</div>

@include('communications.includes.templatepreviewmodal')

@include('communications.includes.templatelistmodal')

<div id="selectTemplateContainer" class="d-none">
    @include('communications.includes.selecttemplate')
</div>

@include('communications.includes.scripts.templatescripts')

<div id="communicationFormContainer"  class="@if($communication->email_editor_type === 'none') d-none @endif">
    <div class="card">
        <div class="card-header">
            @include('communications.includes.back')
        </div>

        <div class="card-body">
            {{ Form::open(['route' => ['communications.update', $communication->id], 'id' => 'form', 'autocomplete' => 'off', 'files' => true]) }}
            {{ Form::hidden('uid', Crypt::encrypt($communication->id)) }}
            {{ method_field('PUT') }}
            
            @include('communications.includes.form', ['method' => 'Edit'])

            {{ Form::close() }}
        
            @include ('communications.includes.attachments')
        </div>
        
        <div class="card-footer">&nbsp;</div>
    </div>
</div>

@include('includes.overlay')
@if (session('empty_content'))
    @push('scripts')
        <script>
            let title = '{{session('empty_content')}}';
            Swal.fire(title,'Please try to add some content.','info')
        </script>
    @endpush
@endif

@include('communications.includes.sendtestemail')
@include('communications.includes.downloadtestpdf')
@endif
@endsection
