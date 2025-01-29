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


    </head>
    <body class="app">
        <div class="container p-4">
            @if( session('START_OVER') )
                @php \App\Classes\Redirections::destroy() @endphp
            @endif

            @if (session('message'))
                @php \App\Classes\Redirections::destroy() @endphp
                <div class="row">
                    <div class="col-sm-12">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {{ session('message') }}
                        </div>
                    </div>
                </div>
            @endif

            @if (session('form-message'))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {{ session('form-message') }}
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
            @endif

            @if (isset($errors) && !request()->is('login') && !request()->is('register'))
                @if($errors->any())
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            <div class="app">
                @yield('content')
            </div>
        </div>
        @include('includes.scripts')
    </body>
</html>
