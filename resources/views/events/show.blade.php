@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('events.settings',$event) !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header text-center">
        @include('widgets.back')
        <div class="d-inline h4">{{ $event->name }}</div>
    </div>
   
    <div class="card-body">
        <div class="row">
            @include('events.includes.event-settings-menu')
            
            <div class="col-md-10 vertical-menu-bar">
                <h1>
                    {{ $total }}
                    <small>@lang('People Checked In This Event')</small>
                </h1>
                <div class="row">
                    <div class="col-sm-12">
                        <h3>Gender</h3>
                        <canvas id="gender"></canvas>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>@lang('Label')</th>
                                    <th>@lang('People')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gender['table'] as $row)
                                <tr>
                                    <td>{{ $row->label ? $row->label : 'Unspecified' }}</td>
                                    <td>{{ $row->total }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-sm-12">
                        <h3>Marital Status</h3>
                        <canvas id="marital_status"></canvas>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>@lang('Label')</th>
                                    <th>@lang('People')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($status['table'] as $row)
                                <tr>
                                    <td>{{ $row->label ? $row->label : 'Unspecified' }}</td>
                                    <td>{{ $row->total }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-sm-12">
                        <h3>Age</h3>
                        <canvas id="age"></canvas>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>@lang('Label')</th>
                                    <th>@lang('People')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($age['table'] as $row)
                                <tr>
                                    <td>{{ $row }}</td>
                                    <td>{{ $age['values'][$loop->index] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
@include('events.includes.actions-event-modal')

@push('scripts')
<script src="{{ asset('js/utils.js') }}"></script>
<script>
    
    var color = Chart.helpers.color;
    var genderChartData = {
        labels: {!! $gender['labels'] !!},
        datasets: [{
            label: '@lang("People by Gender")',
            backgroundColor: color(window.chartColors.yellow).alpha(0.5).rgbString(),
            borderColor: window.chartColors.yellow,
            borderWidth: 1,
            data: {!! $gender['total'] !!}
        }]

    };
    
    var maritalStatusChartData = {
        labels: {!! $status['labels'] !!},
        datasets: [{
            label: '@lang("People by Marital Status")',
            backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
            borderColor: window.chartColors.red,
            borderWidth: 1,
            data: {!! $status['total'] !!}
        }]
    };
    
    var ageChartData = {
        labels: {!! $age['labels'] !!},
        datasets: [{
            label: '@lang("People by Age")',
            backgroundColor: color(window.chartColors.green).alpha(0.5).rgbString(),
            borderColor: window.chartColors.green,
            borderWidth: 1,
            data: {!! $age['total'] !!}
        }]
    };

    window.onload = function() {
        var gender = document.getElementById("gender").getContext("2d");
        window.myBar = new Chart(gender, {
            type: 'bar',
            data: genderChartData,
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },
                title: {
                    display: false,
                    text: 'Gender'
                }
            }
        });
        
        var marital_status = document.getElementById("marital_status").getContext("2d");
        window.myBar = new Chart(marital_status, {
            type: 'bar',
            data: maritalStatusChartData,
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },
                title: {
                    display: false,
                    text: 'Marital Status'
                }
            }
        });
        
        var age = document.getElementById("age").getContext("2d");
        window.myBar = new Chart(age, {
            type: 'bar',
            data: ageChartData,
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },
                title: {
                    display: false,
                    text: 'Age'
                }
            }
        });

    };

</script>
@endpush

@endsection
