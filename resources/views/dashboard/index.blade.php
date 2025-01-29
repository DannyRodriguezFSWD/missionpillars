@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('home') !!}
@endsection

@section('content')
    <div class="text-right pb-2">
        <div class="btn-group" role="group" id="floating-buttons">
            <div class="input-group-btn">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    @lang('Add Widget')
                    <span class="caret"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <button class="dropdown-item" data-toggle="modal" data-target="#widget-types">
                        <span class="fa fa-plus"></span> @lang('Add Widget')
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="dashboard">
        <input type="hidden" name="ADD" value="{{ route('widget.type.add')}}"/>
        <input type="hidden" name="READ" value="{{ route('dashboard.show', ['id' => \Ramsey\Uuid\Uuid::uuid1()])}}"/>
        <input type="hidden" name="UPDATE" value="{{ route('widgets.update', ['id' => ':id:'])}}"/>
        <input type="hidden" name="DELETE" value="{{ route('widgets.destroy', ['id' => ':id:'])}}"/>
        <input type="hidden" name="METRICS" value="{{ route('widget.metrics.type.get')}}"/>
        <input type="hidden" name="ORDER" value="{{ route('dashboard.reorder')}}"/>
        
        <h6>&nbsp;</h6>
        <div class="grid">
            <div class="grid-sizer"></div>
            <div class="gutter-sizer"></div>
        </div>
        
        <div id="templates" style="background: #000; padding: 10px; display: none;">
            @foreach ($widgetTypes->pluck('type') as $type)
                @include('widgets.'.$type.'.index')
            @endforeach
            
            @include('widgets.metrics.index')
            @include('widgets.kpis.fragments.types.money')
            @include('widgets.kpis.fragments.types.number_percent')
        </div>
        
        @include('widgets.includes.delete-widget')
        @include('widgets.includes.widget-types')
        @include('widgets.metrics.fragments.charts')
        @include('widgets.metrics.fragments.edit')
        @include('widgets.metrics.fragments.metrics')
        @include('widgets.kpis.fragments.edit')
        @include('widgets.calendar.fragments.edit')
        
        
    </div>

@include('widgets.welcome.fragments.welcome-tour')


@push('styles')
<link rel="stylesheet" href="{{ asset('js/calendars/fullcalendar/fullcalendar.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('js/calendars/qtip/jquery.qtip.min.css') }}"/>
<style>

    .card{
        margin-bottom: 0;
    }

    .grid:after {
        content: '';
        display: block;
        clear: both;
    }

    .grid-sizer,
    .grid-item { width: 24%; }
    .gutter-sizer { width: 1%; }
    .grid-item-width-12 { width: 100%; }
    .grid-item-width-6 { width:50%; }
    .grid-item-width-4 { width: 33.3333%; }
    .grid-item-width-3 { width: 25%; }


    .grid-item:hover {
        cursor: move;
    }

    .grid-item.is-dragging,
    .grid-item.is-positioning-post-drag {
        /* background: #C90; */
        z-index: 2;
    }

    .packery-drop-placeholder {
        outline: 3px dashed hsla(0, 0%, 0%, 0.5);
        outline-offset: -6px;
        -webkit-transition: -webkit-transform 0.2s;
        transition: transform 0.2s;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/calendars/fullcalendar/moment.min.js') }}"></script>
<script src="{{ asset('js/calendars/fullcalendar/fullcalendar.min.js') }}"></script>
<script src="{{ asset('js/calendars/qtip/jquery.qtip.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('js/packery.pkgd.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/draggabilly.pkgd.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/dashboard/dashboard.run.js')}}"></script>
@endpush()
@endsection
