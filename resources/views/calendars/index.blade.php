@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <h4 class="mb-0"></h4>
        <p>@lang('Contacts')</p>
        <div class="btn-group btn-group" role="group" aria-label="...">
            <a href="{{ route('calendars.create') }}" class="btn btn-primary">
                <i class="fa fa-list-alt"></i>
                @lang('Add New Calendar')
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>@lang('Calendar color')</th>
                    <th>@lang('Calendar name')</th>
                    <th>@lang('Permissions')</th>
                    <th class="text-center">@lang('Events')</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                @foreach($calendars as $calendar)
                    <tr class="clickable-row" data-href="{{ route('calendars.edit', ['id' => $calendar->id]) }}">
                        <td>
                            <span class="badge p-2" style="background-color: {{ $calendar->color }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </td>
                        <td>
                            {{ $calendar->name }}
                        </td>

                        <td>
                            @if( !array_get($calendar, 'public') )
                                <span class="text-danger">
                            (@lang('Private'))
                        </span>
                            @else
                                <span class="text-success">
                            (@lang('Public'))
                        </span>
                            @endif
                        </td>

                        <td class="text-center">
                            {{ $calendar->events->count() }}
                        </td>

                        <td>
                            <span class="icon icon-arrow-right"></span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div>
            {{ $calendars->links() }}
        </div>
    </div>
    <div class="card-footer">
        &nbsp;
    </div>
</div>

@include('calendars.includes.delete-modal')
@endsection
