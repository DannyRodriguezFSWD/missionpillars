<div class="row">
    <div class="col">
        @if (array_get($communication, 'last_action') === 'print')
        <h2>{{ $communication->label }}</h2>
        @else
        <h2>{{ $communication->subject }}</h2>
        @endif
        <h5>For {{ $totalrecipients }} unique  {{ str_plural('recipients', $totalrecipients) }} (May include overlap between email and print)</h5>

        @if(!empty($communication->public_link))
            <div class="input-group my-3">
                {{ Form::text('public_link', $communication->public_link, ['id' => 'public_link', 'readonly', 'class' => 'form-control']) }}
                <div class="input-group-append">
                    <button class="btn btn-outline-info" type="button" onclick="copy('public_link')">
                        <i class="fa fa-copy"></i> Copy Public Link
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <p>
        <h4>{{ $totalemails }} {{ str_plural('Email', $totalemails) }}</h4>
        for {{ $totalemailrecipients }} unique {{ str_plural('recipients', $totalemailrecipients) }}
        </p>
        @if($totalemails)
            <p>
                <a href="{{ route('communications.emailsummary',$communication->id) }}" class="btn btn-primary">View Email Summary</a>
            </p>
        @endif
    </div>
    <div class="col-md-12">
        <p>
        <h4>{{ $totalprinted }} Printed</h4>
        for {{ $totalprintrecipients }} unique {{ str_plural('recipients', $totalprintrecipients) }}
        </p>
        @if($totalprinted)
            <p >
                <a href="{{ route('communications.printsummary',$communication->id) }}" class="btn btn-primary">View Print Summary</a>
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-12">
        @if ($totalEmailsScheduled > 0)
            <div class="alert alert-warning">
                There are <b>{{ $totalEmailsScheduled }} emails</b> scheduled for 
                <b>{{ displayLocalDateTime(array_get($communication, 'time_scheduled'))->format('D, M j g:i A') }}</b>. 
                You will not be able to update or resend this communication until these email are sent.
            </div>
            <div class="text-md-right">
                <button class="btn btn-danger" onclick="cancelSend('{{ route('communications.cancel-send', $communication->id) }}');">
                    Cancel Send/Edit
                </button>
            </div>
        @else
            <div class="text-md-right">
                <a href="{{ route('communications.edit', $communication->id) }}" class="btn btn-lg btn-primary">Edit / Resend Communication</a>
                
                <button class="btn btn-lg btn-danger delete-communication" title="Delete Communication" data-url="{{ route('communications.destroy', $communication->id) }}"><i class="fa fa-trash"></i></button>
            </div>
        @endif
    </div>
</div>

<script>
    $('.delete-communication').tooltip();
    
    $('.delete-communication').click(function() {
        let url = $(this).data('url');
        
        Swal.fire({
            title: 'Are you sure you want to delete this communication?',
            type: 'question',
            showCancelButton: true
        }).then(res => {
            if (res.value){
                $.ajax(url, {
                    method: 'DELETE',
                    beforeSend: function () {
                        Swal.fire('Deleting communication', '', 'info');
                        Swal.showLoading();
                    }
                })
                .done(function (response) {
                    console.log(response);
                    if (response.success) {
                        Swal.fire('Communication deleted successfully', '', 'success');
                        window.location.reload();
                    } else {
                        Swal.fire('Error deleting communication','', 'error');
                    }
                })
                .fail(response => {
                    Swal.fire('Error deleting communication','', 'error');
                });
            }
        });
    });
</script>
