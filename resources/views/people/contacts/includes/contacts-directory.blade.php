<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <input type="text" class="form-control" placeholder="Search people by name or email" id="searchContactsDirectory">
        </div>
    </div>
    <div class="col-md-6">
        <div class="btn-group pull-right mt-md-0 mt-3">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-sort"></i> @lang('Sort')
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item directory-sort @if($sort === 'last_name' && $sortType === 'asc') active @endif" href="#" data-sort="last_name" data-sort-type="asc"><i class="fa fa-sort-alpha-asc mr-3"></i> Last Name</a>
                <a class="dropdown-item directory-sort @if($sort === 'last_name' && $sortType === 'desc') active @endif" href="#" data-sort="last_name" data-sort-type="desc"><i class="fa fa-sort-alpha-desc mr-3"></i> Last Name</a>
                <a class="dropdown-item directory-sort @if($sort === 'first_name' && $sortType === 'asc') active @endif" href="#" data-sort="first_name" data-sort-type="asc"><i class="fa fa-sort-alpha-asc mr-3"></i> First Name</a>
                <a class="dropdown-item directory-sort @if($sort === 'first_name' && $sortType === 'desc') active @endif" href="#" data-sort="first_name" data-sort-type="desc"><i class="fa fa-sort-alpha-desc mr-3"></i> First Name</a>
                <a class="dropdown-item directory-sort @if($sort === 'created_at' && $sortType === 'asc') active @endif" href="#" data-sort="created_at" data-sort-type="asc"><i class="fa fa-sort-numeric-asc mr-3"></i> Created Date</a>
                <a class="dropdown-item directory-sort @if($sort === 'created_at' && $sortType === 'desc') active @endif" href="#" data-sort="created_at" data-sort-type="desc"><i class="fa fa-sort-numeric-desc mr-3"></i> Created Date</a>
            </div>
        </div>
    </div>
</div>

<div class="row" id="contactsDirectory">
@include('people.contacts.includes.contacts-card-list')
</div>

@push('scripts')
    <script>
        $('#searchContactsDirectory').keyup(customDelay(function () {
            directorySearch();
        }, 500));
        
        $('#contactsDirectory').scrollPaginate({
            url: '{{ route('contacts.directory.search') }}',
            lastPage: {{ $contacts->lastPage() }},
            data: {
                @isset ($group)
                    group: {{ array_get($group, 'id') }},
                @endisset
            },
            search: $('#searchContactsDirectory'),
            sort: '.directory-sort.active'
        });
        
        $('.directory-sort').click(function () {
            $('.directory-sort').removeClass('active');
            $(this).addClass('active');
            directorySearch();
        });
        
        function directorySearch() {
            let search = $('#searchContactsDirectory').val();

            customAjax({
                url: "{{ route('contacts.directory.search') }}",
                data: {
                    search: search,
                    sort: $('.directory-sort.active').data('sort'),
                    sortType: $('.directory-sort.active').data('sort-type'),
                    group: @if (request()->routeIs('groups.show') || request()->routeIs('groups.members')) {{ $group->id }} @else null @endif
                },
                success: function (data) {
                    $('#contactsDirectory').html('');
                        
                    if (data.count > 0) {
                        $('#contactsDirectory').html(data.html);
                    } else {
                        $('#contactsDirectory').html('<div class="col-12"><div class="alert alert-info">There are no people that matched your search.</div></div>');
                    }
                    
                    $('#contactsDirectory').parent().find('[name="last_page"]').val(data.lastPage);
                    $('#contactsDirectory').parent().find('[name="last_search"]').val(new Date().getTime());
                }
            });
        }
    </script>
@endpush
