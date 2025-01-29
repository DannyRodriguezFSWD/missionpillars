@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('communications.printsummary',$communication) !!}
@endsection
@section('title')
    Print Commmunication Summary
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            @include('widgets.back')
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12">
                    <h1>Print Communication Summary</h1>
                    <h3>{{ $communication->subject }}</h3>
                </div>
                <div class="col-sm-12">
                    <p>
                        <h4 class="mb-0">{{ $total }} Printed {{ str_plural('Communication',$total) }}</h4>
                        for {{ $totalcontacts }} unique  {{ str_plural('contact', $totalcontacts) }}
                    </p>
                    <p>
                        <div class="btn-group btn-group" role="group" aria-label="...">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-cog"></i>
                                    @lang('Settings')
                                    <span class="caret"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-left">
                                    <a class="dropdown-item" href="{{ route('communications.configureprint', [ 'id' => $communication->id]) }}">
                                        <i class="fa fa-paper-plane"></i>&nbsp;@if($total)Reprint @else Print @endif
                                    </a>
                                    <a class="dropdown-item" href="{{ route('communications.edit', [ 'id' => $communication->id ]) }}">
                                        <i class="fa fa-paper-plane"></i>&nbsp;Edit
                                    </a>
                                </div>
                            </div>
                        </p>
                    </div>
                </p>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>@lang('Contact')</th>
                <th>@lang('City, State/Region')</th>
                <th>@lang('Date')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($printed as $recipient)
                <tr>
                    <td>{{ $recipient->first_name }} {{ $recipient->last_name }}</td>
                    <td>
                        @if($recipient->getMailingAddress())
                            {{ $recipient->getMailingAddress()->city }}, {{ $recipient->getMailingAddress()->region }}
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-pill badge-primary p-2">
                            {{-- TODO attempt to display valid date, possibly from StatementTracking --}}
                            @if ($recipient->pivot->created_at)
                            {{ date("n/j/Y", strtotime($recipient->pivot->created_at)) }}
                            {{-- TODO consider local date handling and use the following line --}}
                            {{-- {{ date("n/j/Y g:i a", strtotime($recipient->pivot->created_at)) }} --}}
                            @endif
                        </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $printed->links() }}
    </div>
    <div class="card-footer">&nbsp;</div>
</div>

@push('scripts')
@endpush

@endsection
