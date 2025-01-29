@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('pledgeforms.edit',$form) !!}
@endsection
@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>

            <div class="card-body">
                {{ Form::model($form, ['route' => ['pledgeforms.update', array_get($form, 'id')], 'method' => 'PUT', 'id' => 'form']) }}
                {{ Form::hidden('uid', Crypt::encrypt(array_get($form, 'id'))) }}
                <div class="row">
                    <div class="col-md-10">
                        <h1 class="">@lang('Edit Pledge Form')</h1>
                    </div>
                    <div class="col-md-2 text-right pb-2">
                        <div class="" id="floating-buttons">
                            <button id="btn-submit-contact" type="submit" class="btn btn-primary">
                                <i class="icons icon-note"></i> @lang('Save')
                            </button>
                        </div>

                    </div>
                </div>
                @include('pledges.forms.includes.form-inputs')
                {{ Form::close() }}
            </div>

        </div>

    </div>

</div>

@push('scripts')

<script type="text/javascript">
    initTinyEditor();
    
    (function () {

        $('#form').on('submit', function (e) {
            var markupStr = tinymce.get("tinyTextarea").getContent();
            
            if (markupStr === '') {
                Swal.fire('Enter the description field','','info')
                return false;
            }
            
            $("input[name='content']").val(markupStr);
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

        $('select[name=campaign_id]').on('change', function(e){
            var url = "{{ route('ajax.get.chartfromcampaign', ['id' => ':id:']) }}";
            var id = $(this).val();
            if( id <= 1 ){
                $('select[name=purpose_id]').attr('disabled', false).val(0);
                return;
            }
            
            $.get(url, {campaign_id: id}).done(function(data){
                $('select[name=purpose_id]').attr('disabled', true).val(data.id);
            }).fail(function(){
                Swal.fire('Oops somethig went wrong!!','','info')
            });
        });

        $('#form').on('submit', function () {
            $('select[name=purpose_id]').attr('disabled', false);
        });

        $('.is_recurring:first').on('click', function (e) {
            $('#recurring').hide();
        });

        $('.is_recurring:last').on('click', function (e) {
            $('#recurring').show();
        });

        $('#recurring').css({display: 'none'});

    })();
</script>
@endpush

@endsection
