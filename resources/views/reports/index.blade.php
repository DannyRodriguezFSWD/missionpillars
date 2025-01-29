@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('accounting.reports.index') !!}
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">Reports</div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('accounting.reports.compare-balance-sheet-by-fund') }}" class="list-group-item"><i class="fa fa-history" aria-hidden="true"></i> Compare Balance Sheet by Fund</a>
                        <a href="{{ route('accounting.reports.balance-sheet') }}" class="list-group-item"><i class="fa fa-history" aria-hidden="true"></i> Balance Sheet</a>
                        <a href="{{ route('accounting.reports.income-statement') }}" class="list-group-item"><i class="fa fa-history" aria-hidden="true"></i> Income Statement</a>
                        <a href="{{ route('reports.income.statement.bymonth') }}" class="list-group-item"><i class="fa fa-history" aria-hidden="true"></i> Income Statement by Month</a>
                        <a href="{{ route('reports.income.statement.byfund') }}" class="list-group-item"><i class="fa fa-history" aria-hidden="true"></i> Income Statement by Fund</a>
                    </div>
                </div>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
    
    
@endsection