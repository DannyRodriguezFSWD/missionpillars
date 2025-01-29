@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('forms.index') !!}
@endsection
@section('content')
<crm-forms :base_url="'{{url('')}}'" :templates_="'{{json_encode($templates)}}'" :permissions_="'{{json_encode($permissions)}}'" :qr_code_link="'{{$qrCodeLink}}'"></crm-forms>
@push('scripts')
    <script src="{{ asset('js/crm-forms-index.js') }}?t={{ time() }}"></script>
@endpush
@endsection
