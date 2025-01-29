@push('scripts')
<script type="text/javascript">
$.ajaxSetup({
    headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var url = $('input[name=url]').val();
var check = "{{ route('events.checkincontacts', ['id' => $split->id, 'action' => 'mobile']) }}";
var uncheck = "{{ route('events.uncheck', ['id' => $split->id, 'contact'=>':id:', 'action' => 'mobile']) }}";
$('#autocomplete').autocomplete({
    source: function( request, response ) {
        // Fetch data
        $.ajax({
            url: "{{ route('contacts.autocomplete') }}",
            type: 'post',
            dataType: "json",
            data: {
                search: request.term
            },
            success: function( data ) {
                response( data );
            }
        });
    },
    minLength: 2,
    select: function( event, ui ) {
        $('[data-table="check-in"] tbody tr').show();
        $('[data-table="check-in"] tbody tr:not([data-id="'+ui.item.id+'"])').hide();
    }
});

$(window).scroll(function (event) {
    var scrollVal = $(document).scrollTop().valueOf();
    if (scrollVal > 100){
        $('#top').fadeIn();
    }
    else{
        $('#top').fadeOut();
    }
});
$('#top').on('click', function(e){
    var body = $("html, body");
    body.stop().animate({scrollTop:0}, 500, 'swing', function() {

    });
});

$('input.checkin').on('click', function(e) {
    var checked = $(this).prop('checked');
    var value = $(this).val();//if valu = 0 there are no tickets purchased/reserved
    var isPaid = parseInt($(this).data('is-paid'));
    var paid = parseInt($(this).data('paid'));
    
    if (value === '0' && isPaid === 1) {
        var popup = $(this).data('popup');
        $(popup).modal('show');
        return false;
    }
   
    if(isPaid === 1 && paid === 0){
        var popup = $(this).data('popup');
        $(popup).modal('show');
        return false;
    }
    
    var has_form = {{ is_null($form) ? 'false' : 'true' }};
    var form_must_be_filled = {{ array_get($event, 'form_must_be_filled', false) ? 'true' : 'false' }};
    var form_filled = parseInt($(this).data('form-filled'));
    
    if( has_form && checked && form_filled === 0){
        var popup = $(this).data('form');
        $(popup).modal('show');
    }
    
    var service = checked ? check : uncheck.replace(':id:', $(this).val());
    
    var data = {
        id: $(this).val(),
        uid: '{!! Crypt::encrypt($split->id) !!}',
        registry_id: $(this).attr('data-registry-id')
    };
    if (!checked){
        data._method = 'DELETE';
    }
/*
    if (has_form){
        var form_must_be_filled = {{ array_get($event, 'form_must_be_filled', false) ? 'true' : 'false' }};
        var form_filled = $(this).data('form_filled') === 1 ? true : false;
        var accept_payments = {{ array_get($form, 'collect_funds') === 0 ? 'false' : 'true' }};
        var already_paid = $(this).data('already_paid') === 1 ? true : false;
        if (form_must_be_filled && checked && !form_filled){
            var form = $(this).data('target');
            if (confirm('@lang("In order to check in into this event, contact must fill required form.\\n¿Do you want to proceed?")')){
                $(form).submit();
            }
            return false;
        }

        if (form_must_be_filled && checked && accept_payments && form_filled && !already_paid){
            var form = $(this).data('pay');
            if (confirm('@lang("In order to check in into this event, contact must finish payment process.\\n¿Do you want to proceed?")')){
                console.log(form);
                $(form).submit();
            }
            return false;
        }
    }
*/
    console.log(service, data);
    $('#overlay').fadeIn();
    $.post(service, data).done(function(data){
        $('#overlay').fadeOut();
    }).fail(function(data){
        console.log(data.responseText);
        Swal.fire('Oops!','something went wrong','error');
    });
});
    
$('button#clear').on('click', function(e){
    $('[data-table="check-in"] tbody tr').show();
    $('input#autocomplete').val('').focus();
});

$('[name="checkin_report_type"]').change(function () {
    $('[data-show]').hide();
    $('[data-show="'+ $(this).val() +'"]').fadeIn();
});

function printCheckinReport() {
    let groupIds = [];
    let reportType = $('[name="checkin_report_type"]:checked').val();
    let fileName = '{{ $reportFileName }}'+ '_' + Date.now();
    
    if (!reportType) {
        Swal.fire('Please select a report method', '', 'error');
        return false;
    }
    
    if (reportType === 'group') {
        $('.group-checkbox:checked').each(function () {
            groupIds.push($(this).data('groupid'));
        });
        
        if (groupIds.length === 0) {
            Swal.fire('Please select at least one group', '', 'error');
            return false;
        }
    }
    
    $('#overlay').fadeIn();
    
    $.ajax({
        type: 'POST',
        url: "{{ route('events.checkin-report', $split) }}",
        data: {
            reportType: reportType,
            groupIds: groupIds
        },
        xhrFields: {
            responseType: 'blob'
        },
        success: function (blob) {
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();

            $('#overlay').fadeOut();
        },
        error: function () {
            Swal.fire("@lang('Oops! Something went wrong.')",'','error');
            $('#overlay').fadeOut();
        }
    });
}
</script>
@endpush
