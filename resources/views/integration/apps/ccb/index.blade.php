@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('integrations.show', $integration) !!}
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            CCB @lang('Integration')
        </h2>
    </div>
    
    <div class="card-body">
        
        <div class="alert alert-success">
            <p class="lead">
                @if (empty(array_get($integration, 'date_last_sync')))
                    @lang('You set up CCB integration correctly. Click on the button below to start getting the data from CCB. Depending on the volume of data, this might take a while.')
                @else
                    @lang('Last sync was done at:') {{ displayLocalDateTime(array_get($integration, 'date_last_sync')) }}
                    <br>
                    @lang('Click on the button below to get the recent changes from CCB.')
                @endif
            </p>
        </div>
        
        <button class="btn btn-primary" id="syncCCB">
            <i class="fa fa-refresh"></i> Sync Now
        </button>
    </div>
</div>

@push('scripts')
<script>
    $('#syncCCB').click(function () {
        customAjax({
            url: "{{ route('ccb.sync', $integration) }}",
            success: function (data) {
                
            }
        });
    });
</script>
@endpush

@endsection
