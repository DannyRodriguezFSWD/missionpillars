<div class="modal fade" id="contact-unsubscribe-modal" tabindex="-1" role="dialog" aria-labelledby="contactUnsubscribeModal" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Manage Phone Unsubscribe')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tr>
                        <th class="border-top-0" colspan="2">
                            <p class="mb-0">Unsubscribed Phone Numbers</p>
                            <p class="mb-0"><small>(toggle off to re-subscribe)</small></p>
                        </th>
                    </tr>
                    @foreach ($contact->unsubscribed_phones as $phone)
                    <tr>
                        <td style="width: 100px;">
                            <label class="c-switch c-switch-label c-switch-primary">
                                <input type="checkbox" name="unsubscribed_phone" class="c-switch-input" checked data-phone="{{ $phone }}" onchange="manageUnsubscribedPhones(this)">
                                <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                            </label>
                        </td>
                        <td>{{ $phone }}</td>
                    </tr>
                    @endforeach
                </table>
                
                <div class="alert alert-info mb-0">
                    <p class="mb-0">In addition they will need to text the word <b>Start</b> to the above phone numbers. 
                        <br>You will need to contact them outside the system and let them know.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function manageUnsubscribedPhones(button) {
        customAjax({
            url: '{{ route('contacts.unsubscribed-phones', $contact) }}',
            data: {
                phone: $(button).attr('data-phone'),
                action: $(button).prop('checked') ? 'unsubscribe' : 'resubscribe'
            },
            success: function (response) {
                $('#unsubscribed-phones').html(response.unsubscribed_from_phones);
                
                Swal.fire(response.message, '', 'success');
            }
        });
    }
</script>
@endpush
