@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    @lang('Transactions vs Total')
                </div>
                <div class="card-body">
                    <canvas id="canvas-1" width="916" height="458" style="display: block; height: 229px; width: 458px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    @lang('By Device')
                </div>
                <div class="card-body">
                    <canvas id="canvas-5" width="916" height="916" style="display: block; height: 458px; width: 458px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    @lang('By Transaction Path')
                </div>
                <div class="card-body">
                    <canvas id="canvas-6" width="916" height="916" style="display: block; height: 458px; width: 458px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script type="text/javascript">
    $(function () {
    var randomScalingFactor = function () { return Math.round(Math.random() * 100) };
    var lineChartData = {
    labels: {!! json_encode($line['labels']) !!},
            datasets: [
            @foreach($line['dataset'] as $data)
                {
                label: "{{ $data['label'] }}",
                        backgroundColor: "{{ $data['backgroundColor'] }}",
                        borderColor: "{{ $data['borderColor'] }}",
                        pointBackgroundColor: "{{ $data['pointBackgroundColor'] }}",
                        pointBorderColor: "{{ $data['pointBorderColor'] }}",
                        data: {!! json_encode($data['serie']) !!}
                },
            @endforeach
            ]
            
    }

    var ctx = document.getElementById('canvas-1');
    var chart = new Chart(ctx, {
    type: 'line',
            data: lineChartData,
            options: {
                    responsive: true,
                    title:{
                            display: true,
                            text: '{{ $from }} to {{ $to }}'
                    },
                    tooltips: {
                            mode: 'index',
                            intersect: false,
                    },
            }
    });
    //PIE CHART
    var pieData = {
    labels: {!! json_encode($pie1['labels']) !!},
            datasets: [{
            data: {!! json_encode($pie1['serie']) !!},
                    backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#ACCE56'
                    ],
                    hoverBackgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#ACCE56'
                    ]
            }]
    };
    var ctx = document.getElementById('canvas-5');
    var chart = new Chart(ctx, {
    type: 'pie',
            data: pieData,
            options: {
                responsive: true,
                title:{
                            display: true,
                            text: '{{ $from }} to {{ $to }}'
                    }
            }
            
    });
    //PIE 2
    var pieData = {
    labels: {!! json_encode($pie2['labels']) !!},
            datasets: [{
            data: {!! json_encode($pie2['serie']) !!},
                    backgroundColor: [
                            '#FF6384',
                            '#ACCE56',
                            '#FFCE56',
                            '#36A2EB'
                    ],
                    hoverBackgroundColor: [
                            '#FF6384',
                            '#ACCE56',
                            '#FFCE56',
                            '#36A2EB'
                    ]
            }]
    };
    var ctx = document.getElementById('canvas-6');
    var chart = new Chart(ctx, {
    type: 'pie',
            data: pieData,
            options: {
                responsive: true,
                title:{
                            display: true,
                            text: '{{ $from }} to {{ $to }}'
                    }
            }
    });
    });
</script>
@endpush

@endsection
