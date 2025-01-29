@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('sb.index') !!}
@endsection
@section('content')
    <accounting-starting-balance 
    v-bind:groups='{{ $groups }}' 
    v-bind:funds='{{ $funds }}' 
    v-bind:sb='{{ $balances }}' 
    v-bind:current-user='{!! Auth::user()->toJson() !!}'
    :permissions = '{!! json_encode($permissions) !!}'
    ></accounting-starting-balance>

    @push('scripts')
        <script src="{{ asset('js/starting-balances.js') }}?t={{ time() }}"></script>
    @endpush
@endsection
