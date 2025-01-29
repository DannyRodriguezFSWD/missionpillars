<div class="row mb-4">
    <div class="col-12">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <input type="text" class="form-control" placeholder="@lang('Search people by name or email')" id="searchContacts">
        </div>
    </div>
</div>

<div class="row" id="contactsTableContainer" style="overflow-y: auto; height: calc(100vh - 322px);">
    <div class="col-12">
        <table class="table table-striped" id="contactsTable">
            <thead>
                <tr>
                    <th></th>
                    <th>@lang('First Name')</th>
                    <th>@lang('Last Name')</th>
                    <th>@lang('Email')</th>
                    <th>@lang('Cellphone')</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    $('#searchContacts').keyup(customDelay(function () {
        let search = $(this).val();

        customAjax({
            url: "{{ route('contacts.directory.search') }}",
            data: {
                search: search,
                membersOfGroup: @if (request()->routeIs('groups.show') || request()->routeIs('groups.members')) {{ $group->id }} @else null @endif,
                view: 'select'
            },
            success: function (data) {
                $('#contactsTable tbody').html('');

                if (data.count > 0) {
                    $('#contactsTable tbody').html(data.html);
                } else {
                    $('#contactsTable tbody').html('<tr><td colspan="5"><div class="alert alert-info">@lang('There are no people that matched your search').</div></td></tr>');
                }

                $('#contactsTable [name="last_page"]').val(data.lastPage);
                $('#contactsTable [name="last_search"]').val(new Date().getTime());
            }
        });
    }, 500));
    
    $('#contactsTable tbody').scrollPaginate({
        scroll: $('#contactsTableContainer'),
        url: '{{ route('contacts.directory.search') }}',
        data: {
            membersOfGroup: @if (request()->routeIs('groups.show') || request()->routeIs('groups.members')) {{ $group->id }} @else null @endif,
            view: 'select'
        },
        search: $('#searchContacts'),
        loadingView: '@include('_partials.loading-tr')'
    });
</script>
@endpush

