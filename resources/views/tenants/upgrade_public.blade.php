@extends('layouts.auth-forms')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mx-8 p-4">
                <div class="card-body">
                    @include('layouts.pricing')
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
