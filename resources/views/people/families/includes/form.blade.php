{{ Form::model($family, ['route' => ['families.update', $family->id], 'method' => 'put', 'id' => 'family-form', 'files' => true]) }}
{{ Form::hidden('uid', Crypt::encrypt(array_get($family, 'id'))) }}

<div class="row">
    <div class="col-lg-6 border-right mb-4 mb-lg-0">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-4">
                                <h5>@lang('Family Name')</h5>
                            </div>

                            <div class="col-12">
                                <div class="form-group {{$errors->has('name') ? 'has-danger':''}}">
                                    {{ Form::text('name', null , ['class' => 'form-control', 'placeholder' => __('Family Name'), 'required' => true, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-control-label">@lang('Envelope Name')</label>
                                <div class="form-group {{$errors->has('envelope_name') ? 'has-danger':''}}">
                                    {{ Form::text('envelope_name', null , ['class' => 'form-control', 'placeholder' => __('Envelope Name'), 'required' => true, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                @include('_partials.cover-image', ['title' => 'Family Image', 'aspectRatio' => 1, 'imagePath' => array_get($family, 'image_path'), 'showRemoveButton' => !is_null(array_get($family, 'image_path'))])
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3 d-table">
                        <label class="h5 mb-0 d-table-cell align-middle">@lang('Family Members')</label>
                        <button class="btn btn-light pull-right" type="button" data-toggle="modal" data-target="#add-contact-to-family-modal">
                            <i class="fa fa-plus"></i> Add People
                        </button>
                    </div>
                </div>
                
                <div id="family-contacts-list">
                    @if ($family->contacts()->count() > 0)
                        @foreach ($family->contacts as $relative)
                            @include ('people.families.includes.contact', ['contact' => $relative])
                        @endforeach
                    @else
                        <div class="col-12">
                            <em>@lang('There are no contacts in this family')</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{ Form::close() }}

@push('scripts')
<script>
    (function(){
        document.getElementById('image').addEventListener('input', function (e) {
            renderImage(e.target)
        })
        
        $('#family-form').on('submit', function (e) {
            let fData = new FormData(document.getElementById('family-form'))
            if (fileImage) fData.set('image', fileImage);
            $('#overlay').show()
            axios({
                url: document.getElementById('family-form').getAttribute('action'),
                method: "POST",
                data: fData,
                headers: { "Content-Type": "multipart/form-data" }
            }).then(function(response){
                Swal.fire('Success!',response.data.message,'success');
                window.location.reload();
            }).catch(function (err){
                let message = Object.values(err.response.data).join('. ')
                Swal.fire('Oops!',message,'info');
            }).finally(function () {
                $('#overlay').hide()
            })
            return false;
            e.preventDefault()
        });
    })();
</script>
@endpush