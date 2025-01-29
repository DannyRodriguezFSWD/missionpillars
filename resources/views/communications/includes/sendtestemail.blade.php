<div class="modal fade" id="send_test_email_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h4 class="font-weight-bold">Send Test Email</h4>
                </div>
                <button class="close" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input class="form-control mr-sm-2 ui-autocomplete-input" type="search" id="send-test-email-search"
                               placeholder="Search contacts" aria-label="Search" autocomplete="off">
                        <br>
                        <h5 class="font-weight-bold">Send test email to:</h5>
                        <ul id="list_of_users"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn_send_test_message">Send</button>
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        let send_test_email_to_users = [];
        function removeUserFromList(id){
            send_test_email_to_users = send_test_email_to_users.filter(user => user.item.id != id)
            updateUserList()
        }
        function updateUserList(){
            let userEl = ''
            send_test_email_to_users.forEach(user => {
                userEl += `<li>${user.item.label} <button class="fa fa-close btn btn-sm" onclick="removeUserFromList(${user.item.id})"></button></li>`
            })
            $('#list_of_users').html(userEl);
        }
        $('#send-test-email-search').autocomplete({
            source: function (request, response) {
                // Fetch data
                $.ajax({
                    url: "{{ route('contacts.autocomplete') }}",
                    type: 'post',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, user) {
                let email_is_empty = user.item.label.includes('()')
                if (email_is_empty) Swal.fire('Contact has no email','Please select a valid contact','info')
                if (send_test_email_to_users.find(usr => usr.item.id == user.item.id ) == undefined && !email_is_empty) {
                    send_test_email_to_users.push(user);
                    updateUserList()
                }
            },
            close(){
                $('#send-test-email-search').val('');
            }
        })
    </script>
@endpush