@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('subscription.invoices') !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header">&nbsp;</div>
    <div class="card-body">
        <h3>@lang('Invoices')</h3>
    </div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>@lang('Date')</th>
                <th>@lang('Reference')</th>
                <th>@lang('Amount')</th>
                <th>@lang('Paid')</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $invoice)
                <tr>
                    <td>{{ displayLocalDateTime(array_get($invoice, 'created_at'))->format('m/d/Y') }}</td>
                    <td>{{ array_get($invoice, 'reference') }}</td>
                    <td>${{ array_get($invoice, 'total_amount') }}</td>
                    <td>
                        {{ $invoice->paid_at ? displayLocalDateTime($invoice->paid_at)->format('m/d/Y') : 'Unpaid' }}
                    </td>
                    <td>
                        <a href="{{ route('subscription.download.invoice', ['id' => array_get($invoice, 'id')]) }}" class="btn btn-link">
                            @lang('Download')
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="card-footer">&nbsp;</div>
</div>

@endsection
