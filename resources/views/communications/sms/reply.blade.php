@extends('layouts.public')

@section('content')

<div class="card animated fadeIn m-4">
    <div class="card-header">
        @lang(' In reply to')
    </div>
    <div class="card-body">
        
        <main class="message">
            <div class="details">
                <div class="header">
                        <div class="from">
                            <strong>{{ array_get($sent, 'createdBy.tenant.organization') }}</strong>
                        </div>
                        <!--
                        <div class="date">
                            {{ displayLocalDateTime(array_get($sent, 'sent_at'))->toDayDateTimeString() }}
                        </div>
                        -->
                    </div>
                    <hr>
                    <div class="content">
                        <p>{{ array_get($sent, 'content.content') }}</p>
                    </div>
                    <hr>
                    {{ Form::open(['route' => ['twilio.sms.send.reply', $id]]) }}
                        <div class="form-group">
                            {{ Form::textarea('content', null, ['class' => "form-control", 'placeholder' => "Enter text here to reply", 'rows' => "6"]) }}
                        </div>
                        <div class="form-group">
                            <button class="btn btn-success btn-block" tabindex="3" type="submit">
                                <span class="fa fa-paper-plane"></span>
                                @lang('Send message')
                            </button>
                        </div>
                    {{ Form::close() }}
                </div>
            </main>
            
        </div>
        
        <div class="card-footer">&nbsp;</div>
    </div>
    
    @push('scripts')
    <script type="text/javascript">
        (function () {
            
        })();
        </script>
        @endpush
        
        @endsection
        