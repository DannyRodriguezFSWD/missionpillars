@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('accounts.index') !!}
@endsection
@section('content')
    <groups 
    v-bind:current-user='{!! Auth::user()->toJson() !!}'
    :permissions='{!! json_encode($permissions) !!}'></groups>
    <!-- template for the modal component -->

    @push('scripts')
        <script src="{{ asset('js/accounts-components.js') }}?t={{ time() }}"></script>
    @endpush
@endsection
