@extends('layouts.auth-forms')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="card">
                <div class="card-header">
                    <h5>@lang('Thank you!')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <p>
                                @lang('Your pledge has been canceled!')
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
</div>

@endsection
