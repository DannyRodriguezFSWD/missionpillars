<header class="c-header c-header-fixed">
    <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show">
        <i class="fa fa-bars c-icon-lg"></i>
    </button>
    <button class="c-header-toggler c-class-toggler mfs-3 d-md-down-none" type="button" data-target="#sidebar"
            data-class="c-sidebar-lg-show" responsive="true">
        <i class="fa fa-bars c-icon-lg"></i>
    </button>
    @if (auth()->user()->can('contact-view'))
    <ul class="c-header-nav mr-auto">
        <li>
            <div class="input-group form-inline my-2 my-lg-0">
                <div class="input-group-prepend">
                    <i class="input-group-text fa fa-user"></i>
                </div>
                <input class="form-control mr-sm-2" type="search" id="global-search" placeholder="Search" aria-label="Search">
            </div>
        </li>
    </ul>
    @endif
    <ul class="c-header-nav ml-auto mr-0 mr-sm-4">
        @if (auth()->user()->can('view-help'))
        <li class="c-header-nav-item">
            <a class="c-header-nav-link"
               href="{{ route('dashboard.help') }}">
                <i class="fa fa-2x fa-question-circle-o"></i>
            </a>
        </li>
        @endif
        <li class="c-header-nav-item dropdown">
            <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="true">
                <img class="img-fluid img-thumbnail rounded-circle" style="width: 40px;" src="{{ auth()->user()->contact->profile_image_src }}" />
            </a>
            <div class="dropdown-menu dropdown-menu-right pt-0">
                <div class="dropdown-header bg-light py-2"><strong>Account</strong></div>
                @if(auth()->user()->can('contact-view'))
                <a class="dropdown-item"
                   href="{{route('contacts.show', ['id' => array_get(auth()->user(), 'contact.id', 0)])}}">
                    <i class="c-icon mr-2 fa fa-user"></i>
                    My Profile
                </a>
                @elseif(auth()->user()->can('edit-profile'))
                <a class="dropdown-item"
                   href="{{route('contacts.edit-profile')}}">
                    <i class="c-icon mr-2 fa fa-user"></i>
                    My Profile
                </a>
                @endif
                
                <a class="dropdown-item"
                   href="{{route('users.edit', ['id' => array_get(auth()->user(), 'id')])}}">
                    <i class="c-icon mr-2 fa fa-gear"></i>
                    Edit Login
                </a>
                @can('update',\App\Models\Tenant::class)
                    <a class="dropdown-item"
                       href="{{route('tenants.edit', ['id' => array_get(auth()->user(), 'tenant.id')])}}">
                        <i class="c-icon mr-2 fa fa-gear"></i>
                        Edit Org Info
                    </a>
                @endcan
                <a class="dropdown-item"
                   href="{{route('system.logout')}}">
                    <i class="c-icon mr-2 icon-login"></i>
                    Sign Out
                </a>
                @if (auth()->user()->can('view-help'))
                <div class="dropdown-item bg-light py-2"><strong>Help</strong></div>
                <a class="dropdown-item"
                   href="{{ route('dashboard.help') }}">
                    <i class="c-icon mr-2 fa fa-life-saver"></i>
                    Help
                </a>
                @endif
            </div>
        </li>
    </ul>
</header>

@hasSection('breadcrumbs')
    @yield('breadcrumbs')
@endif

@if (session('app_demo_alert'))
    <div class="alert alert-success alert-dismissible fade show mb-0" style="position: fixed; width: 100%; z-index: 9999; top: 0; right: 0; left: 0;" role="alert">
        <a href="{{ route('dismiss.alert') }}" class="close">
            <span aria-hidden="true">&times;</span>
        </a>
        <p class="mb-0">
            This is a demo. To sign up for our Online Giving and Management package click <a target="_blank" href="https://www.continuetogive.com">https://www.continuetogive.com</a>. Or to sign up for this system click <a target="_blank" href="https://app.missionpillars.com/register">https://app.missionpillars.com/register</a>
        </p>
    </div>
@endif

@push('scripts')
    <script type="text/javascript">
        $('#global-search').autocomplete({
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
            select: function (event, ui) {
                //console.log(ui);
                //console.log( "Selected: " + ui.item.value + " aka " + ui.item.id );

                var url = "{{ route('contacts.show', ['id' => ':id:']) }}";
                var action = url.replace(':id:', ui.item.id);
                window.location.href = action;
            },
            search: function(event, ui) {
                if ($('span.autocomplete-spinner').length === 0) {
                    $(event.target).after('<span class="mt-1 autocomplete-spinner"><i class="fa fa-spinner fa-spin fa-lg text-warning"></i></span>');
                }
            },
            response: function(event, ui) {
                $(event.target).parent().find('.autocomplete-spinner').remove();
                
                if (!ui.content.length) {
                    var noResult = { value:"",label:"No results found" };
                    ui.content.push(noResult);
                } 
            }
        });
        $('#select-contact-modal').on('shown.coreui.modal', function () {
            $('input[name=contact]').focus()
        });
    </script>
@endpush