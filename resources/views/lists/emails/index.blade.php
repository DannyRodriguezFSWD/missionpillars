@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <h4 class="mb-0">{{ $total }}</h4>
        <p>@lang('Email'){{ $total > 1 ? 's' : '' }}</p>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>@lang('Email')</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach($emails as $email)
                <tr class="clickable-row" data-href="{{ route('lists.email.track', ['list' => array_get($list, 'id'), 'email' => array_get($email, 'id')]) }}">
                    <td>{{ array_get($email, 'subject') }}</td>
                    <td class="text-right"><span class="icon icon-arrow-right"></span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">&nbsp;</div>
</div>

@include('lists.includes.delete-modal')

@endsection
