@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('purposes.create') !!}
@endsection
@section('content')

        <div class="card">
            {{ Form::open(['route' => 'purposes.store']) }}
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-10">
                        <h1 class="">@lang('Create New Purpose')</h1>
                    </div>
                    <div class="col-md-2 text-right">
                        <button id="btn-submit-contact" type="submit" class="btn btn-primary">
                            <i class="icons icon-note"></i> @lang('Save')
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="btn-group" data-toggle="buttons">
                            <label class="btn btn-primary radio active">
                                <input type="radio" name="type" value="Purpose" autocomplete="off" checked> @lang('Purpose')
                            </label>
                            <label class="btn btn-primary radio">
                                <input type="radio" name="type" value="Missionary" autocomplete="off"> @lang('Missionary')
                            </label>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row" id="chart_of_account-missionary">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('contact_id') ? 'has-danger':''}}">
                            <span class="text-danger">*</span> {{ Form::label('contact_id', __('Missionary')) }}
                            @if ($errors->has('contact_id'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('contact_id') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::text('autocomplete', null, ['class' => 'form-control', 'id' => 'autocomplete']) }}
                            {{ Form::hidden('contact_id') }}
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('name') ? 'has-danger':''}}">
                            <span class="text-danger">*</span> {{ Form::label('name', __('Name')) }}
                            @if ($errors->has('name'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('name') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'Off', 'required' => true]) }}
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('description') ? 'has-danger':''}}">
                            <span class="text-danger">*</span> {{ Form::label('description', __('Description')) }}
                            @if ($errors->has('name'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('description') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'autocomplete' => 'Off', 'required' => true]) }}
                        </div>
                    </div>
                </div>
            </div>
            @include('chart_of_accounts.includes.link_purpose_and_income_account')
            {{ Form::close() }}
        </div>

@include('chart_of_accounts.includes.select-contact-modal')

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
        });
        
        $('label.radio').on('click', function(e){
            var value = $('input[name=type]', this).val().toLowerCase();
            
            if( value === 'missionary' ){
                $('#chart_of_account-missionary').fadeIn();
            }
            else{
                $('#chart_of_account-missionary').fadeOut();
            }
        });
        
    })();
</script>
@endpush

@endsection
