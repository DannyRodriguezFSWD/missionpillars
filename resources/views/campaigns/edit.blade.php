@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('campaigns.edit',$campaign) !!}
@endsection
@section('content')
@php
$editdisabled = $from_c2g ? 'disabled ' : '';
@endphp
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                {{ Form::model($campaign, ['route' => ['campaigns.update', $campaign->id], 'method' => 'PUT']) }}
                {{ Form::hidden('uid', Crypt::encrypt($campaign->id)) }}
                <div class="row">
                    <div class="col-md-10">
                        <h1 class="">@lang('Edit Fundraiser')</h1>
                    </div>
                    <div class="col-md-2 text-right">
			@if ($from_c2g) 
			View Only <div style="font-size: x-small">(Edit in Continue to Give)</div>
			@else
                        <button id="btn-submit-contact" type="submit" class="btn btn-primary">
                            <i class="icons icon-note"></i> @lang('Save')
                        </button>
			@endif
                    </div>
                </div>
                
		@if (!$from_c2g) 
                <div class="row">
                    <div class="col-sm-12">
                        <div class="btn-group" data-toggle="buttons">
                            <label class="btn btn-primary radio active">
                                <input {{ $editdisabled }}type="radio" name="page_type" value="Internal" autocomplete="off" checked> @lang('Internal')
                            </label>
                            <label class="btn btn-primary radio">
                                <input {{ $editdisabled }}type="radio" name="page_type" value="Missionary" autocomplete="off"> @lang('Missionary')
                            </label>
                            
                            <label class="btn btn-primary radio">
                                <input {{ $editdisabled }}type="radio" name="page_type" value="External" autocomplete="off"> @lang('External')
                            </label>
                            
                        </div>
                    </div>
                </div>
                <br>
		@endif
                <div class="row" id="chart_of_account-missionary">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('contact_id') ? 'has-danger':''}}">
                            <span class="text-danger">*</span> {{ Form::label('contact_id', __('Missionary')) }}
                            @if ($errors->has('contact_id'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('contact_id') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::text('autocomplete', $contact, ['class' => 'form-control', 'id' => 'autocomplete', 'disabled' => $from_c2g ]) }}
                            {{ Form::hidden('contact_id') }}
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('purpose_id') ? 'has-danger':''}}">
                            <span class="text-danger">*</span> {{ Form::label('purpose_id', __('Purpose')) }}
                            @if ($errors->has('purpose_id'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('purpose_id') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::select(
                                'purpose_id', 
                                $charts, null, 
                                ['class' => 'form-control', 'autocomplete' => 'Off', 'required' => true, 'disabled' => $from_c2g ]) }}
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
                            {{ Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'Off', 'required' => true, 'disabled' => $from_c2g]) }}
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('description') ? 'has-danger':''}}">
                            <span class="text-danger">*</span> {{ Form::label('description', __('Description')) }}
                            @if ($errors->has('description'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('description') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'autocomplete' => 'Off', 'required' => true, 'disabled'=> $from_c2g]) }}
                        </div>
                    </div>
                </div>
                
                {{ Form::close() }}
            </div>
        </div>
    </div>
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
                //var splits = suggestion.value.split(' ');
                //$('input[name=name]').val( splits[0] +' '+ splits[1] );
            }
        }).on('keydown', function(e){
            if(e.which != 13) {
                $('input[name=contact_id]').val('null');
            }
        });
        
        $('label.radio').on('click', function(e){
            isMissionary(this);
        });
    })();
    
    function isMissionary(e){
        
        var value = $('input[name=page_type]', e).val().toLowerCase();
        if( value === 'missionary' ){
            $('#chart_of_account-missionary').fadeIn();
        }
        else{
            $('#chart_of_account-missionary').fadeOut();
        }
    }
    
    @if($errors->has('contact_id') || $campaign->page_type === 'Missionary')
        $('label.radio:eq(1)').click();
    @endif
</script>
@endpush
@endsection
