@php
// requires $event_id to be passed in
@endphp

</php>
<div class="col-md-3">
    <div class="vertical-menu">
        <button class="btn btn-primary my-2" onclick="goToAdvancedContactSearch('create a communication')">
            <span class="icon icon-envelope-letter"></span> @lang('Create Communication')
        </button><br>
        <button class="btn btn-primary my-2" onclick="goToAdvancedContactSearch('send an SMS')">
            <span class="icon icon-speech"></span> @lang('Send SMS')
        </button><br>
        <button class="btn btn-primary my-2" onclick="goToAdvancedContactSearch('export as CSV or Excel')">
            <span class="icon icon-rocket"></span> @lang('Export')
        </button>
    </div>
</div>
<script type="text/javascript">

function goToAdvancedContactSearch(action) {
    let data = {
        name: "event_saveSearch_"+{{ $event_id }},
        search: {
            event_registration: [''+{{ $event_id }}]
        }
    }
    axios.post('{{ route('search.contacts.state.store') }}', data)
    .then((response)=>{
        // console.log(response);
        let state = response.data.state;
        
        Swal.fire({
            title: "Advanced Contact Search",
            text: 'The attendees will automatically be selected on the next screen. From there you will be able to '+action+' from the Actions menu.',
            type: 'info',
            timer: 5000,
            showCancelButton: true
        }).then(function (res) {
            console.log('here');
            location.href = '{{ route('search.contacts.state.show', 99999999) }}'.replace(99999999, state.id)
            if (res.value) {
            }
        });
    })
}

</script>
