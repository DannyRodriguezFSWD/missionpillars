@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                <canvas id="chart"></canvas>
            </div>
            
            <div class="card-footer">&nbsp;</div>
        </div>

    </div>

</div>
<!--/.row-->
@include('pledges.includes.delete-modal')

@push('scripts')
<script type="text/javascript">
    (function () {
    var canvas = $('#chart');
            var chart = new Chart(canvas, {
            type: 'pie',
                    data: {!! json_encode($chart) !!},
                    options: {
                    responsive: true
                    }
            });
    }
    )();
</script>
@endpush

@endsection
