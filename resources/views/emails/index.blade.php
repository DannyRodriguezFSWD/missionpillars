@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">&nbsp;</div>
    <div class="card-body">
        <h4 class="mb-0">{{ $total }}</h4>
        <p>@lang('Emails')</p>
        <div class="btn-group btn-group" role="group" aria-label="...">
            <div class="input-group-btn">
                <a href="{{ route('emails.create') }}" class="btn btn-primary">
                    <i class="fa fa-list"></i> 
                    @lang('Send New Mass Email')
                    <span class="caret"></span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>@lang('Email subject')</th>
                <th>@lang('Sent to list')</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach($emails as $email)
                <tr class="clickable-row" data-href="{{ route('emails.show', ['id' => array_get($email, 'id')]) }}">
                    <td>
                        {{ array_get($email, 'subject') }}
                    </td>
                    <td>
                        {{ array_get($email, 'lists.name') }}
                    </td>
                    <th class="text-right">
                        <span class="icon icon-arrow-right"></span>
                    </th>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
       <div class="card-body">{{ $emails->links() }}</div>
    <div class="card-footer">&nbsp;</div>
</div>

@endsection
