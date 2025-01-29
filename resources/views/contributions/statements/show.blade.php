@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">

        <div class="card">
            <div class="card-header">
                &nbsp;
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>{{ array_get($statement, 'name') }}</h1>
                        <p>
                            @lang('Last Printed for')
                            @if(array_get($statement, 'print_for') === 'contact')
                                <strong>
                                {{ array_get($contact, 'first_name') }}
                                {{ array_get($contact, 'last_name') }}
                                </strong>
                            @else
                                <strong>{{ $print_for[array_get($statement, 'print_for')] }}</strong>
                            @endif
                            at {{ humanReadableDate(array_get($statement, 'created_at')) }}
                        </p>
                    </div>
                    <div class="col-sm-4 text-right pb-2">
                        <div class="" id="floating-buttons">
                            @if(is_null($contact))
                            <a href="{{ route('print-mail.report', ['uuid' => array_get($statement, 'uuid'), 'id' => array_get($statement, 'id')]) }}" class="btn btn-secondary">
                                @lang('View Report')
                            </a>
                            @else
                            <a href="{{ route('print-mail.report', ['uuid' => array_get($statement, 'uuid'), 'id' => array_get($statement, 'id'), 'contact_id' => array_get($contact, 'id')]) }}" class="btn btn-secondary">
                                @lang('View Report')
                            </a>
                            @endif
                            <a href="{{ route('print-mail.edit', ['id' => array_get($statement, 'id')]) }}" class="btn btn-primary">
                                <i class="fa fa-edit"></i> @lang('Edit')
                            </a>
                            @if(is_null($contact))
                            <a href="{{ route('print-mail.preview', ['uuid' => array_get($statement, 'uuid'), 'id' => array_get($statement, 'id')]) }}" class="btn btn-success">
                                <i class="fa fa-print"></i> @lang('Print')
                            </a>
                            @else
                            <a href="{{ route('print-mail.preview', ['uuid' => array_get($statement, 'uuid'), 'id' => array_get($statement, 'id'), 'contact_id' => array_get($contact, 'id')]) }}" class="btn btn-success">
                                <i class="fa fa-print"></i> @lang('Print')
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {!! array_get($statement, 'content') !!}
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>

    </div>
</div>

@endsection
