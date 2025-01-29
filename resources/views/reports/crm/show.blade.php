@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('crmreports.show',$report) !!}
@endsection
@section('content')

@if (in_array(array_get($report, 'id'), [0, 2]))
    @include('reports.crm.includes.report0')
@elseif(array_get($report, 'id') == 1)
    @include('reports.crm.includes.report1')
@elseif(array_get($report, 'id') == 3)
    @include('reports.crm.includes.report3')
@elseif(array_get($report, 'id') == 4)
    @include('reports.crm.includes.report4')
@endif



@push('scripts')
    <script src="https://cdn.datatables.net/v/bs4/dt-1.10.18/sc-1.5.0/datatables.min.js"></script>
    <script src="{{ asset('js/crm-reports-components.js') }}?t={{ time() }}"></script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                "paging":   false,
                "info":     false
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .dataTables_length, .dataTables_info{
            padding-left: 10px;
        }
        .dataTables_filter>label{
            padding-right: 10px;
            width: 100%;
        }
        @if(array_get($report, 'id') == 3 || array_get($report, 'id') == 4)
        table.datatable>tbody>tr>td:last-child,
        table.datatable>tbody>tr>td:nth-last-child(2){
            background: #dff0d8;
        }
        @endif
    </style>
@endpush

@endsection