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
                    <div class="col-sm-10">
                        <h1>@lang('Print Mail')</h1>
                        <p>
                            @lang('Here you can print out mailing.  Somethings to use this for are'):
                        </p>
                        <ul>
                            <li>@lang('Contribution Statement')</li>
                            <li>@lang('Fundraising Letters')</li>
                        </ul>
                        <p>
                            @lang('This process allows you to create a PDF to print and mail to all the people you want')
                            <a class="btn btn-info inline" target="_blank" href="http://support.continuetogive.com/KnowledgeBase/Details/?id=64-Contribution-Statements-with-Mail-Merge">
                                @lang('Click here for more help')
                            </a>
                        </p>
                    </div>
                    <div class="col-sm-2 text-right pb-2">
                        <div class="" id="floating-buttons">
                            <a href="{{ route('print-mail.create') }}" class="btn btn-primary">
                                <i class="fa fa-file-text-o"></i> @lang('Print Mail')
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>@lang('Title')</th>
                        <th>@lang('Printed for')</th>
                        <th>@lang('Date')</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($statements as $statement)
                        <tr class="clickable-row" data-href="{{ route('print-mail.show', ['id' => array_get($statement, 'id')]) }}">
                            <td>{{ array_get($statement, 'name') }}</td>
                            @if(array_get($statement, 'print_for') === 'contact')
                                <td>
                                    {{ array_get($statement, 'contacts.0.first_name') }}
                                    {{ array_get($statement, 'contacts.0.last_name') }}
                                </td>
                            @else
                                <td>
                                    {{ $print_for[array_get($statement, 'print_for')] }}
                                </td>
                            @endif
                            <td>{{ humanReadableDate(array_get($statement, 'created_at')) }}</td>
                            <td class="text-right">
                                <span class="icon icon-arrow-right"></span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                {{ $statements->links() }}
            </div>
            
            <div class="card-footer">&nbsp;</div>
        </div>
        
    </div>
</div>

@endsection
