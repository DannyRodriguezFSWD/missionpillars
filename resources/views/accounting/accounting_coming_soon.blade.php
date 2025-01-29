@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        &nbsp;
    </div>
    <div class="card-body">
        <div class="jumbotron">
            <h1 class="display-3 text-center">
                @if (!session('msg'))
                    @lang('Coming soon')!
                @else
                    @lang('Thanks!')
                @endif                
            </h1>
            <p class="lead text-center">
                @if (!session('msg'))
                    @lang('Get a $20 Gift card for trying the system when it\'s ready!')
                @else
                    @lang('we will keep you updated')
                @endif
            </p>
            <hr class="my-4">
            @if (!session('msg'))
                {{ Form::open(['route' => 'accounting.subscribe.coming.soon', 'class' => 'row']) }}
                <div class="col-sm-3">&nbsp;</div>
                <div class="col-sm-6">
                    <p>@lang('Subscribe and get informed when this awesome feature is available').</p>
                    <div class="form-group">
                        {{ Form::label('Name') }}
                        {{ Form::text('name', array_get(auth()->user(), 'contact.first_name').' '. array_get(auth()->user(), 'contact.last_name'), ['class' => 'form-control']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('Email') }}
                        {{ Form::email('email', array_get(auth()->user(), 'contact.email_1'), ['class' => 'form-control']) }}
                    </div>
                    
                    <button type="submit" class="loading-overlay btn btn-primary btn-lg">
                        @lang('Send me notification when it\'s ready')
                    </button>
                    
                </div>
                {{ Form::close() }}
            @endif
            
        </div>
    </div>
    <div class="card-footer">&nbsp;</div>
</div>

@push('scripts')
<script>
    (function(){
        $('.loading-overlay').on('click', function(e){
            $('#overlay').show();
        });
    })();
</script>
@endpush

@endsection
