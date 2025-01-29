$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var json;
var $grid = null;

$(document).ready(function () {
        
    // Init datepicker
    $('.datepicker').datepicker({
        format: "yyyy-mm-dd",
        autoclose: true
    });

    // HACK: Jiggle fix - see 
    var cf = $('.container-fluid')
    cf.removeClass('container-fluid').addClass('container')
    
    // Init Widgets
    var read = $('input[name="READ"]').val();

    $.ajax(read).done(function (data) {
        json = JSON.stringify(data);
        displayWidgets(data, function () {
            $grid = $('.grid').packery({
                columnWidth: '.grid-sizer',
                gutter: '.gutter-sizer',
                // do not use .grid-sizer in layout
                itemSelector: '.grid-item',
                percentPosition: true
            });
            $grid.find('.grid-item').each(function (i, gridItem) {
                var draggie = new Draggabilly(gridItem);
                // bind drag events to Packery
                $grid.packery('bindDraggabillyEvents', draggie);
            });

            $grid.on('dragItemPositioned', orderItems);
        });

    }).fail(function (data) {
        //alert(data);
    });

    $('.grid').on('click', 'button.resize', function (e) {
        var widgets = JSON.parse(json);
        var idx = $(this).data('index');
        var widget = widgets[idx];
        widget.size = $(this).val();

        var url = $('input[name="UPDATE"]').val().replace(':id:', widget.id);
        var request = {
            method: "POST",
            url: url,
            data: {
                widget: widget,
                _method: 'PUT',
                uid: widget.uid
            }
        };

        $.ajax(request).done(function (data) {
            $('#' + data.id).attr('class', 'grid-item ' + data.size);
            $('.grid').packery();
        }).fail(function (data) {
            alert(data);
        });

    });

    $('.grid').on('click', '.delete-widget', function (e) {
        var idx = $(this).data('index');
        var widgets = JSON.parse(json);
        var widget = widgets[idx];
        $('#delete-widget').find('.modal-body p .widget-name').html(widget.name);
        $('#confirm-delete-widget').data('index', idx);
    });

    $('#confirm-delete-widget').on('click', function (e) {
        var idx = $(this).data('index');
        var widgets = JSON.parse(json);
        var widget = widgets[idx];
        var url = $('input[name="DELETE"]').val().replace(':id:', widget.id);
        var request = {
            method: "POST",
            url: url,
            data: {
                widget: widget,
                _method: 'DELETE',
                uid: widget.uid
            }
        };
        $.ajax(request).done(function (data) {
            $('.grid').packery('remove', $('#' + widget.id)).packery();
        }).fail(function (data) {
            alert(data);
        });
    });

});

function addWidget(id) {
    var url = $('input[name="ADD"]').val();
    var request = {
        method: "POST",
        url: url,
        data: {widget: id}
    };
    var widgets = JSON.parse(json);
    $.ajax(request).done(function (data) {
        var initGridItems = function () {
            $grid.find('.grid-item').each(function (i, gridItem) {
                var draggie = new Draggabilly(gridItem);
                // bind drag events to Packery
                $grid.packery('bindDraggabillyEvents', draggie);
            });
            $grid.on('dragItemPositioned', orderItems);
            widgets.push(data);
        }
        
        switch (data.type) {
            case 'welcome':
                welcome(data, widgets.length, initGridItems);
                break;
            case 'chart':
                charts(data, widgets.length, initGridItems);
                break;
            case 'kpis':
                kpis(data, widgets.length, initGridItems);
                break;
            case 'calendar':
                calendar(data, widgets.length, function (widget) {
                    createCalendar(widget, initGridItems);
                });
                break;
            default:
                initWidget(data, widgets.length, initGridItems);
        }
        
        json = JSON.stringify(widgets);

    }).fail(function (data) {
        alert(data);
    });
}

var metrics = [];
function getMetrics(e) {
    var idx = $(e).data('index');
    var widgets = JSON.parse(json);
    var widget = widgets[idx];

    var url = $('input[name="METRICS"]').val().replace(':id:', widget.id);
    var request = {
        method: "POST",
        url: url,
        data: {
            type: widget.parameters.type
        }
    };

    $('#select-metric-widget').data('index', idx);
    $.ajax(request).done(function (data) {
        metrics = data;
        $('#select-metric-widget').find('.modal-body .list-group').empty();
        data.forEach(function (metric, index) {
            $('#select-metric-widget').find('.modal-body .list-group').append('<li class="list-group-item">' +
                    '<div class="row">' +
                    '<div class="col-sm-8">' +
                    '<h6>' + metric.name + '</h6>' +
                    '<p><small>' + metric.description + '</small></p>' +
                    '</div>' +
                    '<div class="col-sm-4 text-right">' +
                    '<button onclick="changeWidget(this)" data-index="' + index + '" class="btn btn-primary" data-dismiss="modal">' +
                    '<span class="fa fa-check-circle-o"></span> Add' +
                    '</button>' +
                    '</div>' +
                    '</div>' +
                    '</li>');
        });
    }).fail(function (data) {
        alert(data);
    });
}

function changeWidget(e) {
    var idx = $(e).data('index');
    var parent = $('#select-metric-widget').data('index');
    var widgets = JSON.parse(json);

    widgets[parent].name = metrics[idx].name;
    widgets[parent].description = metrics[idx].description;
    widgets[parent].parameters = metrics[idx];

    $('#edit-chart-widget').find('input[name="name"]').val(widgets[parent].parameters.name);
    $('#edit-chart-widget').find('.widget-parameters-name').html(widgets[parent].parameters.name);
    $('#edit-chart-widget').find('.widget-parameters-description').html(widgets[parent].parameters.description);

    json = JSON.stringify(widgets);
}

function editWidget(e) {
    var idx = $(e).data('index');
    var widgets = JSON.parse(json);
    var widget = widgets[idx];

    if (widget.type === 'kpis') {
        $('#edit-kpis-widget').find('.modal-footer button.btn-success').data('index', idx);
        $('#edit-kpis-widget').find('input[name="name"]').val(widget.name);
        var keys = Object.keys(widget.parameters.options.checkboxes);
        keys.forEach(function (key, index) {
            var input = 'input[name="' + key + '"]';
            var value = widget.parameters.options.checkboxes[key] === 'false' ? false : true;
            $('#edit-kpis-widget').find(input).prop('checked', value);
        });
        $('#edit-kpis-widget').modal('show');
    }

    if (widget.type === 'chart') {
        $('#edit-chart-widget').find('.modal-footer button.btn-success').data('index', idx);
        $('#edit-chart-widget').find('.modal-body button.btn-success').data('index', idx);

        $('#edit-chart-widget').find('input[name="name"]').val(widget.name);
        var include_last_year = widget.parameters.include_last_year === '0' ? false : true;
        $('#edit-chart-widget').find('input[name="include_last_year"]').prop('checked', include_last_year);
        $('#edit-chart-widget').find('input[value="' + widget.parameters.period + '"]').prop('checked', true);

        $('#edit-chart-widget').find('.widget-parameters-name').html(widget.parameters.name);
        $('#edit-chart-widget').find('.widget-parameters-description').html(widget.parameters.description);
        $('#edit-chart-widget').modal('show');
    }

    if (widget.type === 'calendar') {
        $('#edit-calendar-widget').find('.modal-footer button.btn-success').data('index', idx);
        $('#edit-calendar-widget').find('input[name="name"]').val(widget.name);
        $('#edit-calendar-widget').find('select[name="calendar_id"]').val(widget.parameters.id);
        $('#edit-calendar-widget').modal('show');
    }
}

function updateWidget(e) {
    var idx = $(e).data('index');
    var widgets = JSON.parse(json);
    var widget = widgets[idx];

    if (widget.type === 'kpis') {
        widget.name = $('#edit-kpis-widget').find('input[name="name"]').val();
        $('#edit-kpis-widget').find('input[type="checkbox"]').each(function (i) {
            var option = $(this).prop('name');
            var value = $(this).prop('checked');
            widget.parameters.options.checkboxes[option] = value;
        });
    }

    if (widget.type === 'chart') {
        widget.name = $('#edit-chart-widget').find('input[name="name"]').val();
        var value = $('#edit-chart-widget').find('input[name="include_last_year"]').prop('checked') ? '1' : '0';
        widget.parameters.include_last_year = value;
        widget.parameters.period = $('#edit-chart-widget').find('input[name="period"]:checked').val();
        if (widget.parameters.period === 'date_range') {
            widget.parameters.from = $('#edit-chart-widget').find('input[name="from"]').val();
            widget.parameters.to = $('#edit-chart-widget').find('input[name="to"]').val();
        }

        widget.metric.chart.datasets.forEach(function (dataset) {
            if (dataset.hasOwnProperty('_meta')) {
                delete dataset['_meta'];
            }
        });

    }

    if (widget.type === 'calendar') {
        widget.name = $('#edit-calendar-widget').find('input[name="name"]').val();
        widget.parameters.id = $('#edit-calendar-widget').find('select[name="calendar_id"]').val();
    }

    var url = $('input[name="UPDATE"]').val().replace(':id:', widget.id);
    var request = {
        method: "POST",
        url: url,
        data: {
            widget: widget,
            _method: 'PUT',
            uid: widget.uid
        }
    };

    $.ajax(request).done(function (data) {
        if (data.type === 'kpis') {
            var $template = $('#' + data.id);
            $template.find('.list-group').empty();
            data.data.kpis.forEach(function (kpi) {
                kpiIndicator($template, kpi, function () {});
            });
        }
        
        if (data.type === 'chart') {
            var $template = $('#' + data.id);
            $template.find('.card-header span:first').html(data.name);
            $template.find('.card-block canvas').remove();
            $template.find('.card-block').append('<canvas></canvas>');
            if (data.metric.type === 'line') {
                createLineChart(data, function () {});
            }
            
            if (data.metric.type === 'pie') {
                createPieChart(data, function () {});
            }
            
        }
        
        if (data.type === 'calendar') {
            var $template = $('#' + data.id);
            $template.find('.card-block .canvas').remove();
            $template.find('.card-block').append('<div class="canvas"></div>');
            createCalendar(data, function () {
                
            });
        }
        widgets[idx] = data;
        var cache = [];
        json = JSON.stringify(widgets, function (key, value) {
            if (typeof value === 'object' && value !== null) {
                if (cache.indexOf(value) !== -1) {
                    // Circular reference found, discard key
                    return;
                }
                // Store value in our collection
                cache.push(value);
            }
            return value;
        });
        
    }).fail(function (data) {
        alert("Error");
    });
}

function addMetric(id) {
    var url = $('input[name="ADD"]').val();
    var request = {
        method: "POST",
        url: url,
        data: {metric: id}
    };

    $.ajax(request).done(function (data) {
        var widgets = JSON.parse(json);
        if (data.type === 'chart') {
            charts(data, widgets.length, function () {
                $grid.find('.grid-item').each(function (i, gridItem) {
                    var draggie = new Draggabilly(gridItem);
                    // bind drag events to Packery
                    $grid.packery('bindDraggabillyEvents', draggie);
                });
                $grid.on('dragItemPositioned', orderItems);
                widgets.push(data);

                var cache = [];
                json = JSON.stringify(widgets, function (key, value) {
                    if (typeof value === 'object' && value !== null) {
                        if (cache.indexOf(value) !== -1) {
                            // Circular reference found, discard key
                            return;
                        }
                        // Store value in our collection
                        cache.push(value);
                    }
                    return value;
                });
            });
        }

    }).fail(function (data) {
        alert(data);
    });
}

function orderItems() {
    var itemElems = $('.grid').packery('getItemElements');
    var order = [];
    $(itemElems).each(function (i, itemElem) {
        var idx = $(itemElem).attr('id');
        if (!isNaN(idx)) {
            current = {id: idx, order: i + 1};
            order.push(current);
        }
    });

    var url = $('input[name="ORDER"]').val();
    $.post(url, {data: order}).done(function (data) {

    }).fail(function (data) {
        alert('Oops! something went wrong');
    });

}

function displayWidgets(widgets, callback) {
    
    widgets.forEach(function (widget, index) {
        switch (widget.type) {
            case 'welcome':
                welcome(widget, index, function () { });
                break;
            case 'chart':
                charts(widget, index, function () { });
                break;
            case 'kpis':
                kpis(widget, index, function (widget) { });
                break;
            case 'calendar':
                calendar(widget, index, function (widget) {
                    createCalendar(widget, function () {
                    });
                });
                break;
            case 'incoming-money': // NOTE this doesn't appear to be in use
                incomingMoney(widget, index, function (widget) { });
                break;
            default:
                initWidget(widget, index, function() {});
        }
    });

    callback();
}

function incomingMoney(widget, index, callback){
    var $template = $('#templates .incoming-money');
    var item = $template.html().replace(/::CLASS::/g, widget.size)
            .replace(/::ID::/g, widget.id)
            .replace(/::ORDER::/g, widget.order)
            .replace(/::NAME::/g, widget.name)
            .replace(/::ONE_TIME_AMOUNT::/g, widget.data.incoming.oneTime.amount)
            .replace(/::ONE_TIME_PERCENT::/g, widget.data.incoming.oneTime.percent)
            .replace(/::PLEDGES_AMOUNT::/g, widget.data.incoming.pledges.amount)
            .replace(/::PLEDGES_PERCENT::/g, widget.data.incoming.pledges.percent)
            .replace(/::RECURRING_AMOUNT::/g, widget.data.incoming.recurring.amount)
            .replace(/::RECURRING_PERCENT::/g, widget.data.incoming.recurring.percent)
            .replace(/::TOTAL_AMOUNT::/g, widget.data.incoming.total.amount)
            .replace(/::TOTAL_PERCENT::/g, widget.data.incoming.total.percent)
            .replace(/::INDEX::/g, index);
    
    if ($grid !== null) {
        $item = $(item);
        $grid.append($item).packery('appended', $item);
    } else {
        $('.grid').append(item);
    }
    callback();
}

function initWidget(widget, index, callback, options) {
    if (!options || !options.css_selector) {
        options = { css_selector: '.'+widget.type }
    }
    
    var parameters = typeof widget.parameters == 'object' || !widget.parameters
    ? widget.parameters : JSON.parse(widget.parameters)
    var $template = $('#templates '+options.css_selector);
    var item = $template.html().replace(/::CLASS::/g, widget.size)
            .replace(/::ID::/g, widget.id)
            .replace(/::ORDER::/g, widget.order)
            .replace(/::NAME::/g, widget.name)
            .replace(/::INDEX::/g, index)
            .replace(/::CONTENT::/g, parameters && parameters.content ? parameters.content : "")

    if ($grid !== null) {
        $item = $(item);
        $grid.append($item).packery('appended', $item);
    } else {
        $('.grid').append(item);
    }
    callback();
}

function welcome(widget, index, callback) {
    return initWidget(widget, index, callback)
}

function calendar(widget, index, callback) {
    initWidget(widget, index, function() {}, { css_selector: '.calendar-widget' } )
    
    callback(widget);
}

function charts(widget, index, callback) {
    initWidget(widget, index, function() {}, { css_selector: '.chart-widget' } )

    if (widget.metric.type === 'line') {
        createLineChart(widget, function () {});
    }

    if (widget.metric.type === 'pie') {
        createPieChart(widget, function () {});
    }

    callback();
}

function createCalendar(widget, callback) {
    var events = widget.data.events;
    var canvas = $('[data-id="' + widget.id + '"]').find('.canvas');
    $(canvas).fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listMonth,listWeek,listDay'
        },
        views: {
            listMonth: { buttonText: 'list month' },
            listWeek: { buttonText: 'list week' },
            listDay: { buttonText: 'list day' }
        },
        defaultDate: new Date(),
        navLinks: true, // can click day/week names to navigate views
        editable: true,
        eventLimit: true, // allow "more" link when too many events
        events: events,
        dayClick: function (date, jsEvent, view) {
        },
        eventClick: function (calEvent, jsEvent, view) {

        },
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
        eventDragStart: function (event, jsEvent, ui, view) {

        },
        eventDragStop: function (event, jsEvent, ui, view) {

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
    callback();
}

function createLineChart(widget, callback) {
    var data_id = widget.id;
    var type = widget.metric.type;
    var data = widget.metric.chart;
    var canvas = $('[data-id="' + data_id + '"]').find('canvas');
    var chart = new Chart(canvas, {
        type: type,
        data: data,
        options: {
            responsive: true,
            tooltips: {
                callbacks: {
                    label: function (tooltipItems, data) {
                        var measurement = widget.metric.measurement;
                        if (measurement === '%') {
                            return tooltipItems.yLabel + ' ' + measurement;
                        } else if (measurement === '$') {
                            var value = parseFloat(tooltipItems.yLabel);
                            return measurement + ' ' + value.formatMoney(2, '.', ',');
                        } else {
                            return measurement + ' ' + tooltipItems.yLabel;
                        }
                    }
                },
                footerFontStyle: 'normal'
            }
        }
    });

    callback(widget);
}

function createPieChart(widget, callback) {
    var canvas = $('[data-id="' + widget.id + '"]').find('canvas');
    var chart = new Chart(canvas, {
        type: widget.metric.type,
        data: widget.metric.chart,
        options: {
            responsive: true,
            title: {
                display: true,
                text: widget.metric.chart.label,
            },
            tooltips: {
                callbacks: {
                    label: function (tooltipItems, data) {
                        var measurement = widget.metric.measurement;
                        var value = data.datasets[tooltipItems.datasetIndex].data[tooltipItems.index]
                        if (measurement === '%') {
                            return value + ' ' + measurement;
                        } else if (measurement === '$') {
                            var value = parseFloat(value);
                            return measurement + ' ' + value.formatMoney(2, '.', ',');
                        } else {
                            return measurement + ' ' + value;
                        }
                    }
                },
                // footerFontStyle: 'normal'
            }
        }
    });

    callback();
}

function kpiIndicator($template, kpi, callback) {
    var status = 'success';
    if (kpi.status === 'down') {
        status = 'danger';
    }
    if (kpi.status === 'equals') {
        status = 'primary';
    }

    if (kpi.type === 'money') {
        var $item = $('#templates .kpi.money');

    }
    if (kpi.type === 'number_percent') {
        var $item = $('#templates .kpi.number_percent');
    }

    if (kpi.type === 'inverted_number_percent') {
        var $item = $('#templates .kpi.number_percent');
        status = 'success';
        if (kpi.status === 'up') {
            status = 'danger';
        }
        if (kpi.status === 'equals') {
            status = 'primary';
        }
    }
    var item = $item.html().replace(/::KPI.TITLE::/g, kpi.title)
            .replace(/::KPI.CURRENT.VALUE::/g, kpi.current.value)
            .replace(/::KPI.LAST.VALUE::/g, kpi.last.value)
            .replace(/::KPI.CURRENT.NUMBER::/g, kpi.current.number)
            .replace(/::KPI.CURRENT.PERCENT::/g, kpi.current.percent)
            .replace(/::KPI.LAST.NUMBER::/g, kpi.last.number)
            .replace(/::KPI.LAST.YEAR::/g, kpi.last.year)
            .replace(/::KPI.STATUS::/g, status)
            .replace(/::KPI.INDICATOR::/g, kpi.status)
            .replace(/::KPI.DESCRIPTION::/g, kpi.description);
    $template.find('ul.list-group').append(item);

    callback();
}

function kpis(widget, index, callback) {
    var $template = $('#templates .kpis');

    widget.data.kpis.forEach(function (kpi) {
        kpiIndicator($template, kpi, function () {

        });
    });

    initWidget(widget, index, function() {})

    callback(widget);
}

Number.prototype.formatMoney = function (c, d, t) {
    var n = this,
            c = isNaN(c = Math.abs(c)) ? 2 : c,
            d = d == undefined ? "." : d,
            t = t == undefined ? "," : t,
            s = n < 0 ? "-" : "",
            i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
            j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};
