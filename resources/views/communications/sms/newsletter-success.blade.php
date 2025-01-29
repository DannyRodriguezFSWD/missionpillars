@extends('layouts.public')

@section('content')
<div style="background-image: url({{ asset('img/widgets/celebration.gif') }}); background-size: contain; background-repeat: no-repeat; background-position: center; height: 100vh;">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="alert alert-success mt-5 h4">
                Thank you for subscribing to {{ array_get($tenant, 'organization') }} newsletter!
            </div>
        </div>
    </div>
</div>

@endsection
