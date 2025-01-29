@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('integrations.show', $integration) !!}
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            Neon @lang('Integration')
        </h2>
    </div>
    
    <div class="card-body">
        
        <div class="alert alert-success">
            <p class="lead mb-0">
                @if (empty(array_get($integration, 'date_last_sync')))
                    @lang('You set up Neon integration correctly. Click on the button below to start getting the data from Neon. Depending on the volume of data, this might take a while.')
                @else
                    @lang('Last synchronization was done at:') {{ date('m/d/Y H:i:s', strtotime(displayLocalDateTime(array_get($integration, 'date_last_sync')))) }}
                    <br>
                    @lang('Click on the button below to get the recent changes from Neon. Depending on the volume of data, this might take a while.')
                @endif
            </p>
        </div>
        
        <button class="btn btn-primary" id="syncNeon">
            <i class="fa fa-refresh"></i> Sync Now
        </button>
    </div>
</div>

@push('scripts')
<script>
    $('#syncNeon').click(function () {
        customAjax({
            url: "{{ route('neon.sync', $integration) }}",
            success: function (response) {
                if (response.success) {
                    Swal.fire('Data synced successfully', '', 'success');
                    window.location.reload();
                }
            }
        });
    });
</script>
@endpush

@endsection
