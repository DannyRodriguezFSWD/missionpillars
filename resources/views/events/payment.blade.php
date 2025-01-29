@extends('layouts.auth-forms')

@section('content')

<div class="row justify-content-center">
    <div class="col-sm-12">
        <p>&nbsp;</p>
        <div class="card mx-md-4">
            <div class="card-body p-md-4">
                <div class="row">
                    <div class="col-sm-12">
                        @if( !is_null($event) )
                        <h3>@lang('Total for') {{ array_get($event, 'name') }}: $ {{ number_format($total, 2) }}</h3>
                        @endif
                        @if( !is_null($form) )
                        <h3>@lang('Total for') {{ array_get($form, 'name') }}: $ {{ number_format($total, 2) }}</h3>
                        @endif
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-sm-12">
                        <iframe id="iframe" style="width: 100%; height: 1000px; border: 0;" src="{{ sprintf( implode('', [env('C2G_IFRAME_URL', '#'), env('PAYMENT_PROCESSING_URL')]), $alt_id, $total, $type, 'PROCESS TRANSACTION', $url, 'TopRedirect' ) }}"></iframe>
                    </div>
                </div>
            </div>
            @include('shared.sessions.start-over-button')
        </div>
    </div>
</div>

@endsection
