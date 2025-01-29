@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">&nbsp;</div>
    <div class="card-body">
        <h4 class="mb-0">{{ $total }}</h4>
        <p>@lang('Lists')</p>
        <div class="btn-group btn-group" role="group" aria-label="...">
            <div class="input-group-btn">
                <a href="{{ route('lists.create') }}" class="btn btn-primary">
                    <i class="fa fa-list"></i> 
                    @lang('Add New List')
                    <span class="caret"></span>
                </a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>@lang('List name')</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach($lists as $list)
                <tr class="clickable-row" data-href="{{ route('lists.show', ['id' => $list->id]) }}">
                    <td>
                        {{ $list->name }}
                    </td>
                    <th class="text-right">
                        <span class="icon icon-arrow-right"></span>
                    </th>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">&nbsp;</div>
</div>

@endsection
