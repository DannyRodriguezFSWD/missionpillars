<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
    <div class="c-sidebar-brand d-lg-down-none px-4">
        <h4>{{auth()->user()->tenant->organization}}</h4>
    </div>
    <ul class="c-sidebar-nav ps ps--active-y">
        @if (auth()->user()->can('contact-create') || auth()->user()->can('transaction-create') || auth()->user()->can('event-create') || auth()->user()->can('contact-create') || auth()->user()->can('communications-menu') || auth()->user()->can('reports-view') || auth()->user()->can('accounting-create'))
        <li class="c-sidebar-nav-item p-4">
            <button class="btn btn-block btn-outline-secondary btn-lg rounded-pill" onclick="$('#new_menu_modal').modal('show')" href="{{ route('dashboard.index') }}">
                <i class="fa fa-plus"></i>&nbsp;@lang('New')
            </button>
        </li>
        @endif
        @if(auth()->user()->can('dashboard-view'))
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="{{ route('dashboard.index') }}">
                    <i class="c-sidebar-nav-icon fa fa-dashboard"></i>@lang('Dashboard')
                </a>
            </li>
        @endif
        @if(auth()->user()->can('view-edit-profile-menu'))
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="{{ route('contacts.edit-profile') }}">
                    <i class="c-sidebar-nav-icon fa fa-user"></i>@lang('Edit Profile')
                </a>
            </li>
        @endif
        <hr style="border-color: white;width: 100%;margin: 0px;border-color: #c8ced3;">
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link alt-link" target="_blank" href="{{
                                implode('', [
                                    env('C2G_MAIN_URL', 'https://www.continuetogive.com/'),
                                 'index.php?moduleType=Module_Home&task=show_login_only'
                                 ])
                             }}">
                <span class="c-sidebar-nav-icon fa fa-star"></span> @lang('Manage Online Giving')
            </a>
        </li>
        @if (auth()->user()->can('view-edit-profile-menu') && array_get(auth()->user()->tenant, 'altId.alt_id'))
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link alt-link" target="_blank" href="{{
                                implode('', [
                                    env('C2G_MAIN_URL', 'https://www.continuetogive.com/'),
                                 array_get(auth()->user()->tenant, 'altId.alt_id'), '/donation_prompt'
                                 ])
                             }}">
                <span class="c-sidebar-nav-icon fa fa-star-o"></span> @lang('Give Now')
            </a>
        </li>
        @endif
        <hr style="border-color: white;width: 100%;margin: 0px;border-color: #c8ced3;">
            @if(auth()->user()->can('contacts-list'))
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('search.contacts') }}">
                        <i class="c-sidebar-nav-icon fa fa-user"></i> @lang('Contacts')
                    </a>
                </li>
            @endif
            @if(auth()->user()->can('contacts-directory'))
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('contacts.directory') }}">
                        <i class="c-sidebar-nav-icon fa fa-user"></i> @lang('Picture Directory')
                    </a>
                </li>  
            @endif
            @if(auth()->user()->can('transactions-menu'))
                @if(auth()->user()->can('transaction-view'))
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ route('transactions.index') }}">
                            <i class="c-sidebar-nav-icon fa fa-dollar"></i>
                            @lang('Transactions')
                        </a>
                    </li>
                @endif
            @elseif(auth()->user()->can('transaction-self'))
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('transactions.self') }}">
                        <i class="c-sidebar-nav-icon fa fa-dollar"></i>
                        @lang('Transactions')
                    </a>
                </li>
            @endif
            @if(auth()->user()->can('pledge-view') && false)
                <li class="c-sidebar-nav-dropdown">
                    <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0);">
                        <i class="c-sidebar-nav-icon fa fa-handshake-o"></i> @lang('Pledges')
                    </a>
                    <ul class="c-sidebar-nav-dropdown-items">
                        <li class="c-sidebar-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('pledges.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-asterisk"></i>
                                @lang('All Pledges')
                            </a>
                        </li>
                        <li class="c-sidebar-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('pledgeforms.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-square"></i>
                                @lang('Pledge Forms')
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            @if( auth()->user()->can('communications-menu') )
                @if(auth()->user()->can('list-view'))
                @endif
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('communications.index') }}">
                        <i class="c-sidebar-nav-icon fa fa-envelope-o"></i>
                        @lang('Mass Email/Print')
                    </a>
                    @if(env('APP_MASS_MESSAGE_AVAILABLE'))
                        <a class="c-sidebar-nav-link" href="{{ route('sms.index') }}">
                            <i class="c-sidebar-nav-icon fa fa-commenting-o"></i>
                            @lang('Texts')
                            
                            @if (auth()->user()->contact->smsReceived()->whereHas('from')->where('read', 0)->count() > 0)
                            <span class="badge badge-success">{{ auth()->user()->contact->smsReceived()->whereHas('from')->where('read', 0)->count() }}</span>
                            @endif
                        </a>
                    @endif
                </li>
            @endif

            @if(auth()->user()->can('group-view'))
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('groups.index') }}">
                        <i class="c-sidebar-nav-icon fa fa-group"></i> @lang('Small Groups')
                    </a>
                </li>
            @endif

            @if(auth()->user()->can('events-view'))
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('events.index') }}">
                        <i class="c-sidebar-nav-icon fa fa-calendar"></i> @lang('Events')
                    </a>
                </li>
            @endif
            
            @if(auth()->user()->can('events-view') && auth()->user()->can('group-view'))
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="{{ route('checkin.index') }}" target="_blank">
                    <i class="c-sidebar-nav-icon fa fa-check-square-o"></i> @lang('Checkin')
                </a>
            </li>
            @endif
            
            @if(auth()->user()->can('form-view'))
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('forms.index') }}">
                        <i class="c-sidebar-nav-icon fa fa-list-alt"></i> @lang('Forms')
                    </a>
                </li>
            @endif
            @if(auth()->user()->can('tasks-view'))
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('tasks.index') }}">
                        <i class="c-sidebar-nav-icon fa fa-check-square"></i> @lang('Tasks')
                    </a>
                </li>
            @endif
            @if(auth()->user()->can('child-check-in-view'))
                <li class="c-sidebar-nav-item">
                    @if(!auth()->user()->tenant->can('crm-child-checkin'))
                        <a class="c-sidebar-nav-link"
                           href="{{ route('subscription.index', ['feature' => 'crm-child-checkin']) }}">
                            <i class="c-sidebar-nav-icon fa fa-futbol-o"></i> @lang('Child Check-In')
                        </a>
                    @else
                        <a class="c-sidebar-nav-link" href="{{route('child-checkin.about')}}">
                            <i class="c-sidebar-nav-icon fa fa-futbol-o"></i> @lang('Child Check-In')
                        </a>
                    @endif
                </li>
            @endif
            <hr style="border-color: white;width: 100%;margin: 0px;border-color: #c8ced3;">
            @if(env('APP_ACCOUNTING_AVAILABLE'))
            @if(auth()->user()->can('accounting-view'))
                <li class="c-sidebar-nav-dropdown">
                    <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0);">
                        <i class="c-sidebar-nav-icon fa fa-credit-card"></i> @lang('Accounting')
                    </a>
                    <ul class="c-sidebar-nav-dropdown-items">
                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('registers.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-dollar"></i>
                                @lang('Transactions')
                            </a>
                        </li>
                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('journal-entries.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-book"></i> @lang('Journal Entry')
                            </a>
                        </li>

                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('journal-entries.fund-transfers') }}">
                                <i class="c-sidebar-nav-icon fa fa-exchange"></i> @lang('Fund Transfers')
                            </a>
                        </li>

                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('accounts.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-list-alt"></i> @lang('Accounts')
                            </a>
                        </li>

                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('sb.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-list-alt"></i> @lang('Starting Balances')
                            </a>
                        </li>

                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('bank-accounts.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-university"></i> @lang('Bank Integration')
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            @endif
            @if (!env('APP_ACCOUNTING_AVAILABLE'))
                <li class="c-header-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('accounting.coming.soon') }}">
                        <i class="c-sidebar-nav-icon fa fa-credit-card"></i> @lang('Accounting')
                    </a>
                </li>
            @endif
            @if (!env('APP_ACCOUNTING_AVAILABLE'))
                <li class="c-header-nav-item">
                    <a class="c-sidebar-nav-link" href="{{ route('accounting.coming.soon') }}">
                        <i class="c-sidebar-nav-icon fa fa-line-chart"></i> @lang('Reports')
                    </a>
                </li>
            @else
                @if(auth()->user()->can('reports-view'))
                    <li class="c-sidebar-nav-dropdown">
                        <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="c-sidebar-nav-icon fa fa-line-chart"></i> @lang('Reports')
                        </a>
                        <ul class="c-sidebar-nav-dropdown-items">
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{ route('crmreports.index') }}">
                                    <i class="c-sidebar-nav-icon fa fa-user-circle-o"></i> @lang('CRM Reports')
                                </a>
                            </li>
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{ route('accounting.reports.index') }}">
                                    <i class="c-sidebar-nav-icon fa fa-credit-card"></i> @lang('Accounting Reports')
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            @endif
            @if(auth()->user()->can('settings-view'))
                <li class="c-sidebar-nav-dropdown">
                    <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0);">
                        <i class="c-sidebar-nav-icon fa fa-gears"></i> @lang('Settings')
                    </a>
                    <ul class="c-sidebar-nav-dropdown-items">
                        @if(auth()->user()->can('purposes-view'))
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{ route('purposes.index') }}">
                                    <i class="c-sidebar-nav-icon fa fa-book"></i> @lang('Purposes')
                                </a>
                            </li>
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{ route('templates.index') }}">
                                    <i class="c-sidebar-nav-icon fa fa-book"></i> PDF Template
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->can('campaign-view'))
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{ route('campaigns.index') }}">
                                    <i class="c-sidebar-nav-icon fa fa-briefcase"></i> @lang('Fundraisers')
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->can('user-view'))
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{ route('users.index') }}">
                                    <i class="c-sidebar-nav-icon fa fa-user-circle-o"></i> @lang('Users')
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->can('role-view'))
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{ route('roles.index') }}">
                                    <i class="c-sidebar-nav-icon fa fa-user-secret"></i> @lang('Roles')
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->can('tag-view'))
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{ route('tags.index') }}">
                                    <i class="c-sidebar-nav-icon fa fa-tags"></i> @lang('Tags')
                                </a>
                            </li>
                        @endif
                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('settings.pledges.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-cog"></i> @lang('Pledges')
                            </a>
                        </li>
                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('settings.sms.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-cog"></i> @lang('SMS')
                            </a>
                        </li>
                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('settings.custom-fields.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-edit"></i> @lang('Custom Fields') <span class="badge badge-success pull-right">New</span>
                            </a>
                        </li>
                        @if (auth()->user()->tenant->has_bloomerang)
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{ route('bloomerang.index') }}"">
                                    <i class="c-sidebar-nav-icon fa fa-arrows-v"></i> @lang('Bloomerang Integration')
                                </a>
                            </li>
                        @endif
                        @can('update',\App\Models\Tenant::class)
                            <li class="c-header-nav-item">
                                <a class="c-sidebar-nav-link" href="{{route('tenants.edit', ['id' => array_get(auth()->user(), 'tenant.id')])}}"">
                                    <i class="c-sidebar-nav-icon fa fa-cog"></i> @lang('Organization Info')
                                </a>
                            </li>
                        @endcan
                        <li class="c-sidebar-nav-dropdown">
                            <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0);">
                                <i class="c-sidebar-nav-icon fa fa-cog"></i> @lang('Subscription')
                            </a>
                            <ul class="c-sidebar-nav-dropdown-items">
                                <li class="c-header-nav-item">
                                    <a class="c-sidebar-nav-link" href="{{ route('subscription.index') }}">
                                        <i class="c-sidebar-nav-icon fa fa-cubes"></i> @lang('Plan details')
                                    </a>
                                </li>
                                <li class="c-header-nav-item">
                                    <a class="c-sidebar-nav-link"
                                       href="{{ route('subscription.show', ['id' => 'payment-methods']) }}">
                                        <i class="c-sidebar-nav-icon fa fa-credit-card"></i> @lang('Payment methods')
                                    </a>
                                </li>
                                <li class="c-header-nav-item">
                                    <a class="c-sidebar-nav-link" href="{{ route('subscription.invoices') }}">
                                        <i class="c-sidebar-nav-icon fa fa-file-text-o"></i> @lang('Invoices')
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            @endif
            @if(env('APP_ENABLE_TOOLS'))
                <li class="c-sidebar-nav-dropdown">
                    <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0);">
                        <i class="c-sidebar-nav-icon fa fa-gears"></i> @lang('Tools')
                    </a>
                    <ul class="c-sidebar-nav-dropdown-items">
                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('tools.index') }}">
                                <i class="c-sidebar-nav-icon fa fa-gears"></i> Testing Home
                            </a>
                        </li>
                        <li class="c-header-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ route('tools.email.viewer') }}">
                                <i class="c-sidebar-nav-icon fa fa-envelope"></i> @lang('Email Viewer')
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            @auth
                @if (auth()->user()->can('view-help'))
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('dashboard.help') }}" class="c-sidebar-nav-link">
                        <i class="fa fa-question-circle c-sidebar-nav-icon"></i>
                        <span class="menu-titles">Help</span>
                    </a>
                </li>
                @endif
                <li class="c-header-nav-item">
                    <a class="c-sidebar-nav-link" href="{{route('system.logout')}}">
                        <i class="c-sidebar-nav-icon fa fa-lock"></i> @lang('Sign Out')
                    </a>
                </li>
            @endauth
    </ul>
</div>
<div class="modal fade" id="new_menu_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <span role="button" class="float-right text-secondary fa fa-close" type="button" data-dismiss="modal" aria-label="Close"></span>
                        </div>
                        <div class="col-md-6">
                            <h4>CRM</h4>
                            <ul style="list-style-type: none">
                                @if(auth()->user()->can('contact-create'))<li><a href="{{route('contacts.create')}}" class="btn btn-link font-weight-bold">Contact</a></li>@endif
                                @if(auth()->user()->can('transaction-create'))<li><a href="{{route('transactions.create')}}" class="btn btn-link font-weight-bold">Transaction</a></li>@endif
                                @if(auth()->user()->can('event-create'))<li><a href="{{route('events.create')}}" class="btn btn-link font-weight-bold">Event</a></li>@endif
                                @if(auth()->user()->can('contact-create'))<li><a href="{{route('communications.create')}}" class="btn btn-link font-weight-bold">Mass Email / Print</a></li>@endif
                                @if(auth()->user()->can('communications-menu'))<li><a href="{{route('communications.create',['create_contribution_statement' => '1'])}}" class="btn btn-link font-weight-bold">Contribution Statement</a></li>@endif
                                @if(auth()->user()->can('reports-view'))<li><a href="{{route('crmreports.index')}}" class="btn btn-link font-weight-bold">Reports</a></li>@endif
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h4>Accounting</h4>
                            <ul style="list-style-type: none">
                                @if(auth()->user()->can('accounting-create'))<li><a href="{{route('registers.index')}}" class="btn btn-link font-weight-bold">Transaction</a></li>@endif
                                @if(auth()->user()->can('accounting-create'))<li><a href="{{route('journal-entries.create')}}" class="btn btn-link font-weight-bold">Journal Entry</a></li>@endif
                                @if(auth()->user()->can('accounting-create'))<li><a href="{{route('journal-entries.fund-transfers.create')}}" class="btn btn-link font-weight-bold">Fund Transfer</a></li>@endif
                                @if(auth()->user()->can('reports-view'))<li><a href="{{route('accounting.reports.index')}}" class="btn btn-link font-weight-bold">Reports</a></li>@endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
    </script>
@endpush