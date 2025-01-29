<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="shortcut icon" href="{{ asset('img/logo-CTG-favicon-opcion-06_favicon.png') }}" type="image/x-icon" />
        @include('includes.styles')
        <style media="print">
        body {
            background-image: none;
        }
        </style>
    </head>
    <body class="cover" style="background-image: url('{{ asset('img/prodotti-86888-reld1f6e99661434d0891c78e9e6d360d34.jpg') }}')">
        @yield('content')
        @include('includes.scripts')
    </body>
</html>
