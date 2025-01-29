@extends('layouts.app')

@section('content')


<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-8">
                <h1>{{ array_get($statement, 'name') }}</h1>
                <p>
                    @lang('Printed for')
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
                    <a href="{{ route('print-mail.edit', ['id' => array_get($statement, 'id')]) }}" class="btn btn-primary">
                        <i class="fa fa-edit"></i> @lang('Edit')
                    </a>
                    <a href="{{ route('print-mail.preview', ['uuid' => array_get($statement, 'uuid'), 'id' => array_get($statement, 'id')]) }}" class="btn btn-success">
                        <i class="fa fa-print"></i> @lang('Print')
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#all_contacts_in_mail_merge" role="tab" aria-selected="false">
                    <span class="icon icon-user-following"></span>
                    @lang('All contacts in mail merge')
                </a>
            </li>
            @if(!is_null($not_in_statement_but_has_donations))
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#all_contacts_not_in_mail_merge_one_transaction" role="tab" aria-selected="false">
                    <span class="icon icon-user"></span>
                    @lang('All contacts not in mail merge with at least one transaction')
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#all_contacts_not_in_mail_merge" role="tab" aria-selected="false">
                    <span class="icon icon-user-unfollow"></span>
                    @lang('All contacts not in mail merge')
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        
        <div class="tab-content">
            <div class="tab-pane active" id="all_contacts_in_mail_merge" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                        @foreach($contacts as $contact)
                            <tr class="clickable-row" data-href="{{ route('contacts.transactions', ['id' => array_get($contact, 'id'), 'st' => array_get($statement, 'id')]) }}">
                                <td>{{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}</td>
                                <td>{{ array_get($contact, 'email_1') }}</td>
                                <td class="text-right">
                                    <span class="icon icon-arrow-right"></span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                
            </div>
            @if(!is_null($not_in_statement_but_has_donations))
            <div class="tab-pane" id="all_contacts_not_in_mail_merge_one_transaction" role="tabpanel">
                <table class="table table-hover table-responsive">
                    <tbody>
                        @foreach($not_in_statement_but_has_donations as $contact)
                        <tr class="clickable-row" data-href="{{ route('contacts.transactions', ['id' => array_get($contact, 'id'), 'st' => array_get($statement, 'id')]) }}">
                            <td>{{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}</td>
                            <td>{{ array_get($contact, 'email_1') }}</td>
                            <td class="text-right">
                                <span class="icon icon-arrow-right"></span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            <div class="tab-pane" id="all_contacts_not_in_mail_merge" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                        @foreach($not_in_statement as $contact)
                            <tr class="clickable-row" data-href="{{ route('contacts.transactions', ['id' => array_get($contact, 'id'), 'st' => array_get($statement, 'id')]) }}">
                                <td>{{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}</td>
                                <td>{{ array_get($contact, 'email_1') }}</td>
                                <td class="text-right">
                                    <span class="icon icon-arrow-right"></span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
        
    </div>
    <div class="card-footer">&nbsp;</div>
</div>

@endsection
