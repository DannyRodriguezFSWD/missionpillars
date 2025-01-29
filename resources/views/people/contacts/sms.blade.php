@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('contacts.send.sms', $contact) !!}
@endsection
@section('content')
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('people.contacts.includes.card-header')
            </div>
            @if (empty(array_get($contact, 'phone_numbers_only')))
            <div class="card-body pb-0">
                <div class="alert alert-danger" role="alert">
                    @lang("This contact does not have a mobile number set for sending SMS. Please update the contact's mobile phone number so we can send an SMS to them.") 
                    <a href="{{ route('contacts.edit', ['id' => array_get($contact, 'id')]) }}">
                        @lang("Update the contact's mobile phone number now")
                    </a>
                </div>
            </div>
            @elseif (!array_get($contact, 'has_us_phone_number'))
            <div class="card-body pb-0">
                <div class="alert alert-danger" role="alert">
                    @lang("This contact has a non US phone number. Please update the contact's mobile phone number so we can send an SMS to them.") 
                    <a href="{{ route('contacts.edit', ['id' => array_get($contact, 'id')]) }}">
                        @lang("Update the contact's mobile phone number now")
                    </a>
                </div>
            </div>
            @else
            <div class="card-body pb-0">
                <div class="row">
                    <div class="col-sm-3">&nbsp;</div>
                    <div class="col-sm-6">
                        @if($has_phone_number)
                        {{ Form::open(['route' => ['contacts.send.sms', $contact->id]]) }}
                        {{ Form::hidden('uid', Crypt::encrypt($contact->id)) }}
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    {{ Form::label('sms_phone_number_id', __('Select which phone number to send this SMS from')) }}
                                    {{ Form::select('sms_phone_number_id', $phoneNumbersSelect, null, ['class'=>'form-control']) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <span class="pull-right small text-muted"><span data-char-limit="content">0</span>/{{ \App\Constants::SMS_CHAR_LIMIT }} characters</span>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::textarea('content', null, ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off', 'rows' => 2, 'placeholder' => 'Start writing...', 'maxlength' => \App\Constants::SMS_CHAR_LIMIT, 'onkeyup' => 'countChar(this, '.\App\Constants::SMS_CHAR_LIMIT.')']) }}
                                </div>
                                <div>
                                    
                                    <button type="button" class="btn btn-success btn-block" id="buy" name="buy" data-toggle="modal" data-target="#sms-modal" style="{{ $has_phone_number ? 'display:none;' : '' }}">
                                            <i class="icons icon-paper-plane"></i> @lang('Send')
                                    </button>
                                    
                                    <button type="submit" id="submit" class="btn btn-success btn-block" name="send" style="{{ !$has_phone_number ? 'display:none;' : '' }}">
                                        <i class="icons icon-paper-plane"></i> @lang('Send')
                                    </button>
                                    
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                        @else
                        <div class="alert alert-info">
                            @lang('Looks like you don\'t have any phone number. If you want to send an sms') <a href="#" id="buy" data-toggle="modal" data-target="#sms-modal">@lang('click here to get one')</a>.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                
                <div class="row">
                    <div class="col-sm-3">&nbsp;</div>
                    <div class="col-sm-6">
                        @push('styles')
                        <link href="{{ asset('css/sms.timeline.custom.css') }}?t={{ time() }}" rel="stylesheet">
                        @endpush
                        <ul class="timeline">
                            @include ('people.contacts.includes.texts')
                        </ul>
                    </div>
                </div>
                
            </div>
            
            <div class="card-body">
                {{ $sms->links() }}
            </div>
            @endif
        </div>
        
    </div>
    <!--/.col-->
    
</div>
<!--/.row-->

<div class="modal fade" id="sms-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Get a phone number')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="crm-communications-viewport">
                    <crm-buy-phone-number 
                        phone="false" 
                        current-contacts="{{ array_get(auth()->user(), 'current_contacts') }}" 
                        stand-alone-component="true" 
                        data-type="redirect"
                        data-target="{{ Request::url() }}"
                        url="{{ sprintf(env('APP_DOMAIN'), array_get(auth()->user(), 'tenant.subdomain') )  }}crm/">
                    </crm-buy-phone-number>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')
<script src="{{ asset('js/crm-communications-components.js') }}?t={{ time() }}"></script>
@endpush



@include('includes.overlay')

@push('scripts')
<script type="text/javascript">
    
    
    (function () {
        $('#submit').on('click', function(e){
            if($('textarea[name="content"]').val().trim() != ''){
                $('#overlay').show();
            }
        });
        
        
    })();
</script>
@endpush
@endsection
