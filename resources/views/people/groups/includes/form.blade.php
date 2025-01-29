{{ Form::hidden('folder_id', $root->id, ['id' => 'parent']) }}
@if( isset($contact) )
{{ Form::hidden('cid',  Crypt::encrypt($contact->id), ['id' => 'cid']) }}
@endif

<div class="card shadow-lg">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <h5>Group Basic Information</h5>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('name', 'Group Name') }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Group Name', 'required' => true, 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('name', __('Group Leader (Who is managing this group?)')) }}
                    {{ Form::text('manager', array_get($manager, 'name'), ['class' => 'form-control autocomplete', 'placeholder' => 'Search by name or email', 'required' => true, 'autocomplete' => 'off']) }}
                    {{ Form::hidden('contact_id', array_get($manager, 'id')) }}
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('description', 'Description') }}
                    {{ Form::textarea('description', null, ['class' => 'form-control tinyTextarea', 'rows' => 2]) }}
                    {{ Form::hidden('content') }}
                </div>
            </div>
        </div>
    </div>
</div>

@include('_partials.cover-image')

<div class="card shadow-lg">
    <div class="card-body">
        <h5>Custom Forms</h5>
        <div class="row">
            <div class="col-12">
                {{ Form::label('form_id', __('Optional registration form')) }}:
                <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;" data-toggle="tooltip" data-placement="right" title="The selected form will be displayed when a person signs up for this group."></i>
                @if ($errors->has('form_id'))
                <span class="help-block text-danger">
                    <small><strong>{{ $errors->first('form_id') }}</strong></small>
                </span>
                @endif
                {{ Form::select('form_id', $forms, null, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
</div>

<div class="card shadow-lg">
    <div class="card-body">
        <h5>Group Location</h5>
        <div class="row">
            <div class="col-12">
                @if ($errors->has('mailing_address_1'))
                <span class="help-block">
                    <small><strong>{{ $errors->first('mailing_address_1') }}</strong></small>
                </span>
                @endif
                <div class="form-group {{$errors->has('mailing_address_1') ? 'has-danger':''}}">
                    {{ Form::label('mailing_address_1', __('Address')) }}
                    {{ Form::text('mailing_address_1', array_get($group, 'addressInstance.0.mailing_address_1') , ['class' => 'form-control', 'placeholder' => __('Adddress'), 'value'=>old('mailing_address_1'), 'autocomplete' => 'off']) }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-lg-4">
                @if ($errors->has('city'))
                <span class="help-block">
                    <small><strong>{{ $errors->first('city') }}</strong></small>
                </span>
                @endif

                <div class="form-group">
                    {{ Form::label('city', __('City')) }}
                    {{ Form::text('city', array_get($group, 'addressInstance.0.city'), ['class' => 'form-control', 'placeholder' => __('City'), 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                @if ($errors->has('region'))
                <span class="help-block">
                    <small><strong>{{ $errors->first('region') }}</strong></small>
                </span>
                @endif

                <div class="form-group">
                    {{ Form::label('region', __('State/Region')) }}
                    {{ Form::text('region', array_get($group, 'addressInstance.0.region'), ['class' => 'form-control', 'placeholder' => __('Region'), 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                @if ($errors->has('country'))
                <span class="help-block">
                    <small><strong>{{ $errors->first('country') }}</strong></small>
                </span>
                @endif

                <div class="form-group {{$errors->has('country') ? 'has-danger':''}}">
                    {{ Form::label('country_id', __('Country')) }}
                    {{ Form::select('country_id', $countries, array_get($group, 'addressInstance.0.country_id'), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    initTinyEditor();
    
    (function(){
        document.getElementById('image').addEventListener('input',function (e) {
            renderImage(e.target)
        })
        
        $('#form').on('submit', function (e) {
            let markupStr = tinymce.get("description").getContent();
            $("input[name='content']").val(markupStr);
            
            let fData = new FormData(document.getElementById('form'))
            if (fileImage) fData.set('image',fileImage);
            $('#overlay').show()
            axios({
                url: document.getElementById('form').getAttribute('action'),
                method: "POST",
                data: fData,
                headers: { "Content-Type": "multipart/form-data" },
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
        
        $('.autocomplete').autocomplete({
            source: function( request, response ) {
                // Fetch data
                $.ajax({
                    url: "{{ route('contacts.autocomplete') }}",
                    type: 'post',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function( data ) {
                        response( data );
                    }
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                $('input[name=contact_id]').val(ui.item.id);
            }
        });
    })();
    
    $(document).ready(function () {
        $('[name="manager"]').prop('autocomplete', 'none');
    });
</script>
@endpush