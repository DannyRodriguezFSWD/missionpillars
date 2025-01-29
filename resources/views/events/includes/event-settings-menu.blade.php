<div class="col-md-2 bg-white pt-2 pb-4 mx-3 mx-md-0 vertical-menu-container">
    <div class="vertical-menu no-print">
        <h3 class="text-center mb-2">{{array_get($event,'name')}}</h3>
        @can('update',$split)
        <a href="{{ route('events.checkin', ['id' => array_get($split, 'id')]) }}" class="{{ Request::route()->getName() == 'events.checkin' ? 'active' : '' }}">
            <span class="icon icon-check"></span> @lang('Check In')
        </a>
        @endcan
         @can('update',$split)
        <a href="{{ route('events.settings', ['id' => array_get($split, 'id')]) }}" class="{{ Request::route()->getName() == 'events.settings' ? 'active' : '' }}">
            <span class="icon icon-settings"></span> @lang('Settings')
        </a>
        @endcan
        <a href="#" data-toggle="modal" data-target="#actions-event-modal">
            <span class="fa fa-share-alt"></span> @lang('Share URL')
        </a>
        @if(Route::is('events.settings'))
            <button class="btn btn-block btn-success" type="submit"><i class="fa fa-save"></i> Save Event</button>
        @endif
        @if(false)
        @can('show',$split)
        <a href="{{ route('events.show', ['id' => array_get($split, 'id')]) }}" class="{{ Request::route()->getName() == 'events.show' ? 'active' : '' }}">
            <span class="icon icon-chart"></span> @lang('Overview')
        </a>
        @endcan
        @can('show',$split)
        <a href="{{ route('events.attenders', ['id' => array_get($split, 'id')]) }}" class="{{ Request::route()->getName() == 'events.attenders' ? 'active' : '' }}">
            <span class="icon icon-user"></span> @lang('Attenders')
        </a>
        @endcan
        @can('show',$split)
        <a href="{{ route('events.report', ['id' => array_get($split, 'id')]) }}" class="{{ Request::route()->getName() == 'events.report' ? 'active' : '' }}">
            <span class="icon icon-graph"></span> @lang('Report')
        </a>
        @endcan
        <!--
        <a href="{{ route('events.alerts', ['id' => array_get($split, 'id')]) }}" class="{{ Request::route()->getName() == 'events.alerts' ? 'active' : '' }}">
            <span class="icon icon-bell"></span> @lang('Alerts')
        </a>
        -->
        <!--
        <a href="{{ route('events.volunteers', ['id' => array_get($split, 'id')]) }}" class="{{ Request::route()->getName() == 'events.volunteers' ? 'active' : '' }}">
            <span class="icon icon-people"></span> @lang('Volunteers')
        </a>
        -->
        @endif
    </div>
</div>