@extends('layouts.app')

@section('content')


    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    @include('people.contacts.includes.card-header')
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="lead bg-faded">@lang('Recurring Transactions')</p>
                            <div class="pledges-info">
                                @include('shared.transactions.recurring.index')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <!--/.col-->

    </div>
    <!--/.row-->

@endsection
