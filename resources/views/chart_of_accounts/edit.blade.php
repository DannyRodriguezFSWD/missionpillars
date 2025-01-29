@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('purposes.edit',$chart) !!}
@endsection
@section('content')

    <div class="card">
        {{ Form::model($chart, ['route' => ['purposes.update', $chart->id], 'method' => 'PUT']) }}
        <div class="card-header">
            @include('widgets.back')
        </div>
        <div class="card-body">
            
            {{ Form::hidden('uid', Crypt::encrypt($chart->id)) }}
            <div class="row">
                <div class="col-md-10">
                    <h1 class="">@lang('Edit Purpose')</h1>
                </div>
                <div class="col-md-2 text-right">
                    <button id="btn-submit" type="submit" class="btn btn-primary">
                        <i class="icons icon-note"></i> @lang('Save')
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group {{$errors->has('name') ? 'has-danger':''}}">
                        {{ Form::label('name', __('Title')) }}
                        @if ($errors->has('name'))
                        <span class="help-block text-danger">
                            <small><strong>{{ $errors->first('name') }}</strong></small>
                        </span>
                        @endif
                        @if ($from_c2g)
                            {{ Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'Off', 'readonly' => true]) }}
                        @else
                            {{ Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'Off']) }}
                        @endif
                        @if ($from_c2g)
                            <span class="help-block text-danger">
                            <small><b>You must edit the name of this purpose inside of Continue to Give to ensure they stay in sync</b></small>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {{ Form::label('type', __('Type')) }}
                        @if($from_c2g)
                            {{ Form::select('type', ['Organization' => 'Organization', 'Purpose' => 'Purpose', 'Missionary' => 'Missionary'], null, ['class' => 'form-control','disabled' => true]) }}
                        @else
                            {{ Form::select('type', ['Purpose' => 'Purpose', 'Missionary' => 'Missionary'], null, ['class' => 'form-control']) }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="row" id="missionary">
                <div class="col-sm-12">
                    <div class="form-group {{$errors->has('contact_id') ? 'has-danger':''}}">
                        {{ Form::label('contact_id', __('Missionary')) }}
                        @if ($errors->has('contact_id'))
                        <span class="help-block text-danger">
                            <small><strong>{{ $errors->first('contact_id') }}</strong></small>
                        </span>
                        @endif
                        {{ Form::text('autocomplete', $autocomplete, ['class' => 'form-control', 'id' => 'autocomplete']) }}
                        {{ Form::hidden('contact_id', null) }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group {{$errors->has('description') ? 'has-danger':''}}">
                        {{ Form::label('description', __('Description')) }}
                        @if ($errors->has('name'))
                        <span class="help-block text-danger">
                            <small><strong>{{ $errors->first('description') }}</strong></small>
                        </span>
                        @endif
                        @if ($from_c2g)
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'autocomplete' => 'Off', 'readonly' => true]) }}
                        @else
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'autocomplete' => 'Off']) }}
                        @endif
                        @if ($from_c2g)
                            <span class="help-block text-danger">
                            <small><b>You must edit the description of this purpose inside of Continue to Give to ensure they stay in sync</b></small>
                        </span>
                        @endif

                    </div>
                </div>
            </div>
            
            @if (array_get($chart, 'parent_purposes_id'))
            <div class="row">
                <div class="col-12">
                    <div class="d-flex">
                        <label for="purpose_is_active" class="c-switch c-switch-label  c-switch-primary c-switch-sm mr-2">
                            @if ($from_c2g)
                            {{ Form::hidden('is_active') }}
                            {{ Form::checkbox('is_active', 1, null, ['id'=>'purpose_is_active', 'class'=>"c-switch-input", 'disabled' => true]) }}
                            @else
                            {{ Form::checkbox('is_active', 1, null, ['id'=>'purpose_is_active', 'class'=>"c-switch-input"]) }}
                            @endif
                            <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

                        </label>
                        <label for="purpose_is_active">Is Active <i class="fa fa-question-circle text-info cursor-help" data-toggle="tooltip" title="Making this purpose inactive means that you will no longer be able to create new trasnactions for this purpose."></i></label>
                    </div>
                    @if ($from_c2g)
                    <span class="help-block text-danger">
                        <small><b>You must edit the name of this purpose inside of Continue to Give to ensure they stay in sync</b></small>
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @include('chart_of_accounts.includes.link_purpose_and_income_account')

        {{ Form::close() }}
    </div>

@include('chart_of_accounts.includes.select-contact-modal')
@push('styles')
<style>
    #missionary{
        display: none;
    }
</style>
@endpush
@push('scripts')
<script type="text/javascript">
    (function(){
        
        $('#autocomplete').autocomplete({
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
                $('input[name=contact_id]').val( ui.item.id );
                var splits = ui.item.value.split(' ');
                $('input[name=name]').val( splits[0] +' '+ splits[1] );
            }
        }).on('keydown', function(e){
            if(e.which != 13) {
                $('input[name=contact_id]').val('null');
            }
        });

        $('select[name=type]').on('change', function(e){
            $('input[name=contact_id]').val('null');
            var value = $(this).val();
            if(value.toLowerCase() == 'missionary'){
                $('#missionary').show();
            }
            else{
                $('#missionary').hide();
            }
        }).change();
        $('input[name=contact_id]').val('{{array_get($chart,'contact_id')}}');
        
        var top = 35;
        $(window).scroll(function () {
            var y = $(this).scrollTop();
            var button = $('#btn-submit');
            if (y >= top) {
                button.css({
                    'position': 'fixed',
                    'top': '60px',
                    'right': '36px',
                    'z-index': '99'
                });
            } else {
                button.removeAttr('style')
            }
        });
        
    })();
</script>

@endpush

@endsection
