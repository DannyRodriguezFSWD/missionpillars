@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                {{ Form::model($template, ['route' => 'forms.store', 'id' => 'form']) }}
                <div class="form-group">
                    {{ Form::label('image', __("Form's cover image")) }}<br/>
                    {{ Form::file('image') }}
                </div>
                <div class="form-group">
                    {{ Form::label('name', __("Form's name")) }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Untitled Form'), 'required' => true]) }}
                </div>
                <div class="form-group">
                    {{ Form::checkbox('collect_funds', true) }}
                    {{ Form::label('collect_funds', __('Allow this form to ask for credit card information')) }}
                </div>
                <div class="form-group">
                    {{ Form::checkbox('show_total', true) }}
                    {{ Form::label('show_total', __('Show total amount')) }}
                </div>
                {{ Form::hidden('json', null, ['id' => 'json']) }}
                
            </div>
        </div>
        
        <div class="row" id="collect-funds">
            <div class="col-sm-12">
                <h3>@lang('Funds for')</h3>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    {{ Form::label('campaign_id', __('Fundraiser')) }}
                    {{ Form::select('campaign_id', $campaigns, null, ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group {{$errors->has('purpose_id') ? 'has-danger':''}}">
                    {{ Form::label('purpose_id', __('Purpose')) }}
                    @if ($errors->has('purpose_id'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('purpose_id') }}</strong></small>
                    </span>
                    @endif

                    {{ Form::select('purpose_id', $charts, null, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-sm-12">
                <div class="build-wrap"></div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js" integrity="sha256-xNjb53/rY+WmG+4L6tTl9m6PpqknWZvRt0rO1SRnJzw=" crossorigin="anonymous"></script>
<script src="{{ asset('js/forms/form-builder.min.js')}}"></script>
<script src="{{ asset('js/forms/form-render.min.js')}}"></script>
<script>
    (function () {
        @if($template->collect_funds)
            $('#collect-funds').show();
        @endif
        
        $('input[name=collect_funds]').on('click', function(e){
            $('#collect-funds').toggle();
            $('input[name=show_total]').prop('checked', $(this).prop('checked'));
        });
        
        @include('forms.includes.form-builder')
        
        var options = {
            inputSets: inputSets,
            controlOrder: controlOrder,
            defaultFields: defaultFields,
            disableFields: disableFields,
            onSave: function(e, formData){
                json.value = formData;
                $('#form').submit();
            }
        };

        $('.build-wrap').formBuilder(options);
        
    })();
</script>
@endpush

@endsection
