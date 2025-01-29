@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('transactions.index') !!}
@endsection
@section('content')

<div id="crm-transactions">
    <crm-transactions
        v-bind:endpoint="'{{ route('transactions.index') }}'"
        v-bind:display="'transactions'"
        v-bind:link_purposes_and_accounts="{{ $link_purposes_and_accounts ? 1 : 0 }}"
        v-bind:pledge_id="0"
        :contacts_link="'{{ route('contacts.index')}}'"
        :permissions='{!! json_encode($permissions) !!}'
        :folders="{{ $tags }}"
    >
    </crm-transactions>
</div>

@push('scripts')
    @if(session('doCreate'))
        <script>
            let doCreate = true;
        </script>
    @endif
<script src="{{ asset('js/crm-transactions.js') }}"></script>
@endpush

@endsection
