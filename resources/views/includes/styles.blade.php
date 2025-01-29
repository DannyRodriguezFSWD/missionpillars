<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/simple-line-icons.css') }}" rel="stylesheet">

@if (Route::currentRouteName() === 'contacts.edit')
<link href="{{ asset('css/choices.min.css') }}" rel="stylesheet">
@endif

{{--<link href="{{ asset('css/coreui.css') }}" rel="stylesheet">--}}
<link href="{{ asset('css/custom.css') }}?t={{ time() }}" rel="stylesheet">
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
<link rel="stylesheet" href="{{asset('css/app.css')}}">
@stack('styles')

<style>
    /*Datatable Select Entries Collision With CORE UI / Bootstrap Hack*/
    .dataTables_wrapper .custom-select{
        padding: 0.375rem 1.75rem 0.375rem 0.75rem !important;
    }
    /*End Datatable Select Entries Collision With CORE UI / Bootstrap Hack*/
</style>