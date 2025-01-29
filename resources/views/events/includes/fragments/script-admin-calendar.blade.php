dayClick: function (date, jsEvent, view) {
            var d = new Date(date);
            var url = "{{ route('events.create', ['date' => '-date-']) }}".replace('-date-', d.toMysqlFormat());
            window.location.href = url;
        },
        eventClick: function (calEvent, jsEvent, view) {
            var base = "{!! route('events.getdata', ['id' => ':id:']) !!}";
            var url = base.replace(':id:', calEvent.id);
            $('#overlay').show();

            $.get(url).done(function (data) {
                $('input[name=uid]').val(data.uid);
                var action = $('input[name=url]').val().replace(':id:', data.event.id);

                $('#form').attr('action', action);
                $('#btn-overview').attr('href', action);
                $('#btn-settings').attr('href', action + '/settings');
                $('#btn-check-in').attr('href', action + '/checkin');

                $('input[name=share]').val($('input[name=link]').val().replace(':id:', data.event.uuid));

                $('#actions-event-modal').find('#btn-overview').parent().show()
                if(!data.permission.show) $('#actions-event-modal').find('#btn-overview').parent().hide()

                $('#actions-event-modal').find('#btn-delete').parent().show()
                if(!data.permission.delete) $('#actions-event-modal').find('#btn-delete').parent().hide()

                $('#actions-event-modal').find('#btn-settings').parent().show()
                if(!data.permission.update) $('#actions-event-modal').find('#btn-settings').parent().hide()

                $('#actions-event-modal').find('#btn-check-in').parent().show()
                if(!data.permission.update) $('#actions-event-modal').find('#btn-check-in').parent().hide()

                var modal = $('#actions-event-modal');
                modal.find('.modal-title').html(data.event.name);
                modal.modal();
            })
                    .fail(function () {
                        Swal.fire("@lang('Oops! An error has occurred')",'','error');
                    })
                    .always(function () {
                        $('#overlay').hide();
                    });
        },