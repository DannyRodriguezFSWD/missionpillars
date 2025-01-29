{{ Form::model($contact, ['route' => ['contacts.update.about', array_get($contact, 'id')], 'method' => 'PUT']) }}
{{ Form::hidden('uid', Crypt::encrypt(array_get($contact, 'id'))) }}
<div class="row">
    <div class="col-md-12 text-right pb-2">
        <div class="" id="floating-buttons">
            <button id="btn-submit-contact" type="submit" class="btn btn-primary"><i class="icons icon-note"></i> Save</button>
        </div>
    </div>
</div>
<p>&nbsp;</p>
<div class="row">
    <div class="col-md-12">
        <p class="lead bg-faded">@lang('Background Info')</p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        {{ Form::textarea('background_info', array_get($contact, 'background_info'), ['class' => 'form-control']) }}
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <hr>
    </div>
</div>

{{ Form::close() }}