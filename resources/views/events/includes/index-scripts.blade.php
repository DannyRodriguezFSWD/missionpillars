@push('scripts')
<link rel="stylesheet" href="{{ asset('js/calendars/fullcalendar3_10/fullcalendar.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('js/calendars/qtip/jquery.qtip.min.css') }}"/>

<script src="{{ asset('js/calendars/fullcalendar3_10/moment.min.js') }}"></script>
<script src="{{ asset('js/calendars/fullcalendar3_10/fullcalendar.min.js') }}"></script>
<script src="{{ asset('js/calendars/qtip/jquery.qtip.min.js') }}"></script>

<script type="text/javascript">
(function () {
    
    function twoDigits(d) {
        if (0 <= d && d < 10)
            return "0" + d.toString();
        if (-10 < d && d < 0)
            return "-0" + (-1 * d).toString();
        return d.toString();
    }
    Date.prototype.toMysqlFormat = function () {
        return this.getUTCFullYear() + "-" + twoDigits(1 + this.getUTCMonth()) + "-" + twoDigits(this.getUTCDate());
    }

    $('#new-event').on('click', function (e) {
        $('.datepicker:not(.export-tickets-filter)').val(new Date().toMysqlFormat());
    });

    var calendar = $('#calendar').fullCalendar({
        
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'list,month,agendaWeek,agendaDay,listMonth,listWeek,listDay'
        },
        views: {
            listMonth: { buttonText: 'list month' },
            listWeek: { buttonText: 'list week' },
            listDay: { buttonText: 'list day' },
            list: {
                duration: { days: 365 },
                listDayAltFormat: 'dddd',
                buttonText: 'upcoming',
                title: 'Test'
            }
        },
        lazyFetching: false,
        defaultView: 'list',
        defaultDate: new Date(),
        navLinks: true, // can click day/week names to navigate views
        editable: true,
        eventLimit: true, // allow "more" link when too many events
        events: function (start, end, timezone, callback) {
            var params = {
                start: start.toDate().toMysqlFormat(),
                end: end.toDate().toMysqlFormat(),
                calendar: $('#show-calendar').data('calendar'),
                public: {{ $public ? 'true' : 'false' }},
                calendars: "{{ implode('-', array_pluck($calendars, 'id')) }}"
            };
            console.log("{{ route('events.ajax.get') }}", params);
            
            $.get("{{ route('events.ajax.get') }}", params).done(function (result) {
                callback(result);
            }).fail(function (result) {
                console.log(result);
            });
            
        },
        @if(!$public)
            @include('events.includes.fragments.script-admin-calendar')
        @else
            @include('events.includes.fragments.script-public-calendar')
        @endif
        eventMouseover: function (event, jsEvent, view) {
            $(this).css({
                'border-color': '#000',
                'opacity': 0.8
            });
        },
        eventMouseout: function (event, jsEvent, view) {
            $(this).css({
                'border-color': 'transparent',
                'opacity': 1
            });
        },
        eventDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
            revertFunc();
        },
        eventRender: function (event, element) {
            element.qtip({
                content: {
                    text: '<small>' + event.start.format('LLLL') + '</small>',
                    title: event.title + ' starts at '
                },
                style: "qtip-bootstrap",
                position: {
                    at: 'bottom left'
                }
            });
        }
    });
    
    $('.calendar-option').on('click', function(e){
        var caption = $(this).html();
        var id = $(this).data('id');
        var color = $(this).data('color');
        var border = id === 0 ? $(this).data('border') : $(this).data('color');
        
        $('#show-calendar').data('calendar', id).css({
            'background-color': color,
            'border-color': border
        }).html(caption);
        
        calendar.fullCalendar( 'refetchEvents' );
    });
    
})();
</script>
@endpush