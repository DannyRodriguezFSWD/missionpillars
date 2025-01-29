eventClick: function (calEvent, jsEvent, view) {
    var base = "{!! route('events.getdata', ['id' => ':id:']) !!}";
    var url = base.replace(':id:', calEvent.id);
    console.log(url);
    $('#overlay').show();
    $.get(url).done(function (data) {
        var target = $('#event-modal');
        target.find('div.tickets').hide();
        target.find('h4.modal-title span').html(data.event.template.name);
        $('.modal-header').css("background-color", data.event.template.calendar.color);
        target.find('span.date').html(data.date);
        
        
        if(data.event.template.description == '' || !data.event.template.description){
            target.find('.description').hide();
        }
        else{
            target.find('.description span').html(data.event.template.description);
            target.find('.description').show();
        }
        
        if(data.address == '<br/><br/><br/>'){
            target.find('div.address').hide();
        }
        else{
            target.find('div.address span').html(data.address);
            target.find('div.address').show();
        }
        

        if( data.event.template.is_paid === 1 ){
            var ul = "";
            data.event.template.ticket_options.forEach(function(item){
                ul += '<li class="list-group-item d-flex justify-content-between align-items-center">';
                ul += item.name;
                ul += '<span class="badge badge-primary badge-pill p-2">$';
                ul += item.price;
                ul += '</span>';
                ul += '</li>';
            });

            target.find('ul.list-group').html(ul);
            target.find('div.tickets').show();
        }
        
        if( data.event.template.allow_auto_check_in && !data.event.template.allow_reserve_tickets ){
            var caption = 'Check in';
        }
        else{
            var caption = 'Register now';
        }
        
        if( data.event.template.allow_reserve_tickets ){
            target.find('.info-reserve-tickets').show();
        }
        else{
            target.find('.info-reserve-tickets').hide();
        }

        var url = target.find('div.modal-footer input[name="url"]').val().replace(':ID:', data.event.uuid);
        target.find('input[name="next_url"]').val(url);
        target.modal('show');
    })
    .fail(function (result) {
        console.log(result.responseText);
        Swal.fire("@lang('Oops! An error has occurred')",'','error');
    })
    .always(function () {
        $('#overlay').hide();
    });
},