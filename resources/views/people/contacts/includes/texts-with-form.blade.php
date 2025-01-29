<div class="row overflow-auto px-3" style="height: calc(100vh - 395px);">
    <div class="col-12">
        <ul class="timeline" id="textsThread">
            @include ('people.contacts.includes.texts')
        </ul>
    </div>
</div>

<hr>

@if (!$hasPhoneNumber)
<div class="alert alert-warning">
    <p>Currently you don't have any phone numbers you are able to send from. Click the button below to get one.</p>
    <a href="{{ route('settings.sms.index') }}" class="btn btn-primary">
        <i class="fa fa-phone"></i> Buy Phone Number
    </a>
</div>
@elseif (empty(array_get($contact, 'phone_numbers_only')))
<div class="alert alert-danger">
    <p>This contact does not have a mobile number set for sending SMS. Please update the contact's mobile phone number so we can send an SMS to them.</p>
    <a href="{{ route('contacts.edit', ['id' => array_get($contact, 'id')]) }}" class="btn btn-primary">
        <i class="fa fa-edit"></i> Update the contact's mobile phone number now
    </a>
</div>
@elseif (!array_get($contact, 'has_us_phone_number'))
<div class="alert alert-danger">
    <p>This contact has a non US phone number ({{ array_get($contact, 'cell_phone') }}). Please update the contact's mobile phone number so we can send an SMS to them.</p>
    <a href="{{ route('contacts.edit', ['id' => array_get($contact, 'id')]) }}" class="btn btn-primary">
        <i class="fa fa-edit"></i> Update the contact's mobile phone number now
    </a>
</div>
@else
{{ Form::open(['route' => ['contacts.send.sms', $contact->id], 'id' => 'sendTextForm']) }}

<div class="row mt-3">
    <div class="col-12">
        <div class="form-group">
            {{ Form::label('sms_phone_number_id', __('From:'), ['class' => 'd-inline']) }}
            {{ Form::select('sms_phone_number_id', $phoneNumbersSelect, null, ['class'=> 'form-control d-inline w-50']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <p>
            @lang('To'): {{ array_get($contact, 'full_name') }} ({{ array_get($contact, 'cell_phone') }})
            <span class="pull-right small text-muted"><span data-char-limit="content">0</span>/{{ \App\Constants::SMS_CHAR_LIMIT }} characters</span>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {{ Form::textarea('content', null, ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off', 'rows' => 2, 'placeholder' => 'Write your reply here', 'maxlength' => \App\Constants::SMS_CHAR_LIMIT, 'onkeyup' => 'countChar(this, '.\App\Constants::SMS_CHAR_LIMIT.')']) }}
        </div>
        <button type="button" class="btn btn-success btn-block" name="send" onclick="sendText();">
            <i class="icons icon-paper-plane"></i> @lang('Send')
        </button>
    </div>
</div>
{{ Form::close() }}
@endif