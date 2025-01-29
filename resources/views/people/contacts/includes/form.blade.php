@if(is_null($contact))
    {{ Form::open(['route' => 'contacts.store', 'id' => 'form', 'files' => true]) }}
    {{ Form::hidden('entry_id', $entry_id) }}
@else
    {{ Form::open(['route' => ['contacts.destroy', array_get($contact, 'id')], 'method' => 'DELETE', 'id' => 'form-delete-contact']) }}
    {{ Form::hidden('uid', Crypt::encrypt(array_get($contact, 'id'))) }}
    {{ Form::close() }}

    {{ Form::model($contact, ['route' => ['contacts.update', $contact->id], 'method' => 'put', 'id' => 'form', 'files' => true]) }}
    {{ Form::hidden('uid', $uid) }}
@endif

{{ Form::hidden('mainContactForm', 1) }}

<div class="row">
    <div class="col-md-12 text-right pb-2">
        <div class="" id="floating-buttons">
            <button id="btn-submit-contact" type="submit" class="btn btn-primary"><i class="icons icon-note"></i> @lang('Save')</button>
            @if(!is_null($contact))
                @can('delete',$contact)
                    <button type="button" class="btn btn-danger" id="btn-delete-contact">
                        <span class="fa fa-trash-o"></span>
                        @lang('Delete contact')
                    </button>
                @endcan
            @endif
        </div>
    </div>
</div>

<div class="card shadow-lg">
    <div class="card-header">
        @include('people.contacts.includes.card-header')
    </div>
    <div class="card-body">
        <div class="personal-info">
            @include('people.contacts.includes.personal-info')
        </div>
    </div>
</div>

@can('create', $contact)
<div class="row" data-showIfPerson="true" style="@if (array_get($contact, 'type') === 'organization') display: none; @endif">
    <div class="col-12">
        <div class="card shadows-lg">
            <div class="card-body">
                @include('people.contacts.includes.family-info')
            </div>
        </div>
    </div>
</div>
@endcan

<div class="row">
    <div class="col-md-6">
        @include('_partials.cover-image', ['title' => 'Profile Picture', 'aspectRatio' => 1])
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5 class="mb-4">@lang('Background Info')</h5>
                <div class="row">
                    <div class="col-md-12">
                        {{ Form::textarea('background_info', array_get($contact, 'background_info'), ['class' => 'form-control']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card shadow-lg">
    <div class="card-body">
        <div class="contact-info">
            @include('people.contacts.includes.contact-info')
        </div>
    </div>
</div>

<div class="card shadow-lg">
    <div class="card-body">
        <div class="other-info">
            <h5 class="mb-4">@lang('Other Info')</h5>
            
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        {{ Form::checkbox('confirmed_no_allergies', true, array_get($contact, 'confirmed_no_allergies', false)) }}
                        {{ Form::label('confirmed_no_allergies', __('Confirmed No Allergies')) }}
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    @if ($errors->has('allergies'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('allergies') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group {{$errors->has('allergies') ? 'has-danger':''}}">
                        {{ Form::label('allergies', __('Allergies')) }}
                        {{ Form::text('allergies', null , ['class' => 'form-control', 'placeholder' => __('Allergies'), 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    @if ($errors->has('deceased'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('deceased') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group {{$errors->has('deceased') ? 'has-danger':''}}">
                        {{ Form::label('deceased', __('Deceased')) }}
                        {{ Form::text('deceased', null, ['class' => 'form-control datepicker readonly', 'placeholder' => __('Deceased'), 'autocomplete' => 'off']) }}
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    @if ($errors->has('membership_type'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('membership_type') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group {{$errors->has('membership_type') ? 'has-danger':''}}">
                        {{ Form::label('membership_type', __('Membership Type')) }}
                        {{ Form::select('membership_type', [
                                            'Guest' => __('Guest'),
                                            'Attender' => __('Attender'), 
                                            'Member' => __('Member'), 
                                            'Business' => __('Business'), 
                                            'Awana parents don\'t attend VCF' => __('Awana parents don\'t attend VCF'),
                                            'Youth Parents don\'t attend VCF' => __('Youth Parents don\'t attend VCF'),
                                            'Youth do not attend VCF' => __('Youth do not attend VCF'),
                                            'Recovery Ranch' => __('Recovery Ranch'),
                                            'Attends another church' => __('Attends another church')
                                        ], null, ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    @if ($errors->has('membership_start_date'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('membership_start_date') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group {{$errors->has('membership_start_date') ? 'has-danger':''}}">
                        {{ Form::label('membership_start_date', __('Membership Start Date')) }}
                        {{ Form::text('membership_start_date', null, ['class' => 'form-control datepicker readonly', 'placeholder' => __('Membership Start Date'), 'autocomplete' => 'off']) }}
                    </div>
                </div>
                
                <div class="col-md-6">
                    @if ($errors->has('membership_end_date'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('membership_end_date') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group {{$errors->has('membership_end_date') ? 'has-danger':''}}">
                        {{ Form::label('membership_end_date', __('Membership Stop Date')) }}
                        {{ Form::text('membership_end_date', null, ['class' => 'form-control datepicker readonly', 'placeholder' => __('Membership Stop Date'), 'autocomplete' => 'off']) }}
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::checkbox('active', true, array_get($contact, 'active', false)) }}
                        {{ Form::label('active', __('Is Active')) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::checkbox('baptized', true, array_get($contact, 'baptized', false)) }}
                        {{ Form::label('baptized', __('Baptized')) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::checkbox('is_private', true, array_get($contact, 'is_private', false)) }}
                        {{ Form::label('is_private', __('Hide from picture directory and groups')) }}
                    </div>
                </div>
                
                <div class="col-md-6">
                    @if ($errors->has('background_check'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('background_check') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group {{$errors->has('background_check') ? 'has-danger':''}}">
                        {{ Form::label('background_check', __('Background Check')) }}
                        {{ Form::text('background_check', null, ['class' => 'form-control datepicker readonly', 'placeholder' => __('Background Check'), 'autocomplete' => 'off']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (!is_null($contact))
{{ Form::close() }}
@endif

<div class="card shadow-lg">
    <div class="card-body">
        @if(is_null($contact))
        <div class="address-info">
            <h5 class="mb-4">@lang('Address')</h5>
            @include('addresses.includes.address-info')
        </div>
        {{ Form::close() }}
        @else
        <div class="address-info">
            @include('people.contacts.includes.address-info')
        </div>
        @endif
    </div>
</div>

@if(count($customFields) > 0)
<form id="custom-fields-form">
    @include('people.contacts.includes.custom-field-sections')
</form>

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        if ($('.mp_custom_field_multiselect').length > 0) {
            new Choices('.mp_custom_field_multiselect', {
                removeItemButton: true,
                searchResultLimit: 10
            }); 
        }
    });
</script>
@endpush
@endif

@if(count($customFieldsImported) > 0)
<div class="card shadow-lg">
    <div class="card-body">
        <div class="custom-fields-imported">
            <h5 class="mb-4">@lang('Custom Fields (Imported)')</h5>
            
            <div class="row">
                @foreach($customFieldsImported as $field)
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label(array_get($field, 'customField.name')) }}
                        {{ Form::text(array_get($field, 'customField.name'), array_get($field, 'value'), ['class'=>'form-control','disabled'=>true]) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    (function(){
        document.getElementById('image').addEventListener('input',function (e) {
            renderImage(e.target)
        })
        
        $('#form').on('submit', function (e) {
            let fData = new FormData(document.getElementById('form'))
            if (fileImage) fData.set('image',fileImage);
            
            @if(count($customFields) > 0)
                let customFieldsData = {};

                $('.mp_custom_field').each(function (i, el) {
                    let fieldCode = $(el).attr('name');
                    customFieldsData[fieldCode] = $(el).val();
                });
                
                fData.append('customFieldsData', JSON.stringify(customFieldsData));
            @endif
            
            $('#overlay').show()
            axios({
                url: document.getElementById('form').getAttribute('action'),
                method: "POST",
                data: fData,
                headers: { "Content-Type": "multipart/form-data" }
            }).then(function(response){
                Swal.fire('Success!',response.data.message,'success');
                if (response.data.redirect) window.location.href = response.data.redirect;
                else window.location.reload();
            }).catch(function (err){
                let message = Object.values(err.response.data).join('. ')
                Swal.fire('Oops!',message,'info');
            }).finally(function () {
                $('#overlay').hide()
            })
            return false;
            e.preventDefault()
        });
        
        $('#btn-delete-contact').on('click', function(e){
            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure you want to delete this contact?",
                type: 'question',
                showCancelButton: true,
            }).then(res => {
                if (res.value){
                    $('#form-delete-contact').submit();
                }
            })
        });
    })();
</script>
@endpush
