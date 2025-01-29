<div class="modal fade" id="download_test_pdf_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h4 class="font-weight-bold">Download Test PDF</h4>
                </div>
                <button class="close" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input class="form-control mr-sm-2 ui-autocomplete-input" type="search" id="download-test-pdf-search"
                               placeholder="Search contacts" aria-label="Search" autocomplete="off">
                        <br>
                        <h5 class="font-weight-bold">Download test pdf for:</h5>
                        <ul id="list_of_users_pdf"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn_download_test_pdf">Download</button>
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        let download_test_pdf_for_users = [];
        function removeUserFromListPdf(id){
            download_test_pdf_for_users = download_test_pdf_for_users.filter(user => user.item.id != id)
            updateUserListPdf()
        }
        function updateUserListPdf(){
            let userEl = ''
            download_test_pdf_for_users.forEach(user => {
                userEl += `<li>${user.item.label} <button class="fa fa-close btn btn-sm" onclick="removeUserFromListPdf(${user.item.id})"></button></li>`
            })
            $('#list_of_users_pdf').html(userEl);
        }
        $('#download-test-pdf-search').autocomplete({
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
                if (download_test_pdf_for_users.find(usr => usr.item.id == user.item.id ) == undefined) {
                    download_test_pdf_for_users.push(user);
                    updateUserListPdf()
                }
            },
            close(){
                $('#download-test-pdf-search').val('');
            }
        })
    </script>
@endpush