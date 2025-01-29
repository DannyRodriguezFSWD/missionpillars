@extends('layouts.auth-forms')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="card">
                <div class="card-header">
                    <h5>@lang('Cancel Pledge')</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => ['pledges.cancel.pledge', array_get($pledge, 'id')], 'method' => 'DELETE']) }}

                    <div class="row">
                        <div class="col-sm-12">
                            <p>
                                @lang('Hi') 
                                <strong>{{ array_get($contact, 'preferred_name', array_get($contact, 'first_name').' '.array_get($contact, 'last_name')) }}</strong>,
                                    @lang('we sorry you want to cancel your pledge for') <strong>{{ array_get($chart, 'name') }}</strong>
                            </p>
                            <p>
                                @lang('Click on cancel button to confirm this action')
                            </p>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-exclamation-triangle"></i> @lang('Cancel pledge')
                            </button>
                        </div>
                    </div>
                    {{ Form::close() }}

                </div>

                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
</div>

@endsection
