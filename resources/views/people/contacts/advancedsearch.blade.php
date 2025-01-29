@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('contacts_advance_search') !!}
@endsection
@section('content')
    <div id="overlay" class="app-loader d-block">
        <div class="spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
    </div>

    @can('create',\App\Models\Contact::class)
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="savedSearchesDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-filter"></i>
                    @if ($selectedState)
                    {{ array_get($selectedState, 'name') }}
                    @else
                    @lang ('All Contacts')
                    @endif
                </button>
                <div class="dropdown-menu" aria-labelledby="savedSearchesDropdown">
                    <a class="dropdown-item @if (!$selectedState) active @endif" href="{{ route('search.contacts') }}">
                        @lang('All Contacts')
                    </a>
                    <div class="dropdown-divider"></div>
                    
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" placeholder="Search Lists" id="searchLists">
                    </div>
                    
                    <div id="statesContainer" class="overflow-auto" style="max-height: 300px;">
                        @foreach ($states as $state)
                        <a class="dropdown-item @if (array_get($selectedState, 'id') === array_get($state, 'id')) active @endif" href="{{ route('search.contacts', ['state_id' => array_get($state, 'id')]) }}" data-stateName="{{ array_get($state, 'name') }}">
                            {{ array_get($state, 'name') }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-secondary toggleFiltersBtn" data-toggle="tooltip" title="{{ __('Show Filters') }}">
                <i class="fa fa-filter"></i>
            </button>
            
            <a href="{{ route('contacts.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> @lang('Contact')
            </a>
            
            @if (auth()->user()->can('contact-update') && auth()->user()->can('contact-create'))
            <a href="{{ route('contacts.import', ['id' => 'file']) }}" class="btn btn-secondary" data-toggle="tooltip" title="{{ __('Import Contacts') }}">
                <i class="fa fa-upload"></i>
            </a>
            @endif
            
            @if (auth()->user()->can('contact-update'))
            <a href="{{ route('merge.index') }}" class="btn btn-secondary" data-toggle="tooltip" title="{{ __('Merge Contacts') }}">
                <i class="fa fa-compress"></i>
            </a>
            @endif
            
            <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @lang('Actions')
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" onclick="datatableAction('.buttons-excel')">
                        <i class="fa fa-file-excel-o"></i>&nbsp;@lang('Excel')
                    </a>
                    <a class="dropdown-item" href="#" onclick="datatableAction('.buttons-csv')">
                        <i class="fa fa-file-excel-o"></i>&nbsp;@lang('CSV')
                    </a>
                    <a class="dropdown-item" href="#" onclick="datatableAction('.buttons-print')">
                        <i class="fa fa-print"></i>&nbsp;@lang('Print')
                    </a>
                    <a class="dropdown-item" href="#" onclick="datatableAction('.buttons-pictureDirectory')">
                        <i class="fa fa-file-pdf-o"></i>&nbsp;@lang('Picture Directory')
                    </a>
                    <a class="dropdown-item" href="#" onclick="datatableAction('.buttons-add_remove_tag')">
                        <i class="fa fa-tags"></i>&nbsp;@lang('Add or Remove Tags')
                    </a>
                    <a class="dropdown-item" href="#" onclick="datatableAction('.buttons-emailOrPrint')">
                        <i class="fa fa-envelope"></i>&nbsp;@lang('Create Communication')
                    </a>
                    <a class="dropdown-item" href="#" onclick="datatableAction('.buttons-sendSms')">
                        <i class="fa fa-commenting"></i>&nbsp;@lang('Send SMS')
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <div class="card">
        <div class="card-body" id="contactsDatatable">
            {{-- <input type="text" name=""> --}}
            <div id="advance_contact_search">
                <div id="searchFilters" style="display: none">
                    <div class="card">
                        <h3 class="card-header bg-primary text-white">
                            <button class="btn btn-sm btn-secondary pull-left mr-2" onclick="resetFilters();">
                                <i class="fa fa-undo"></i> Reset
                            </button>
                            <button class="btn btn-sm btn-success pull-left mr-2" onclick="datatableAction('.buttons-saveSearch')">
                                <i class="fa fa-save"></i> Save As New List
                            </button>
                            @if ($selectedState)
                            <button class="btn btn-sm btn-warning pull-left mr-2" onclick="datatableAction('.buttons-updateSearch')">
                                <i class="fa fa-edit"></i> Update List "{{ array_get($selectedState, 'name') }}"
                            </button>
                            <button class="btn btn-sm btn-danger pull-left" onclick="deleteState()">
                                <i class="fa fa-trash"></i> Delete List "{{ array_get($selectedState, 'name') }}"
                            </button>
                            @endif
                            <span class="small pull-right cursor-pointer toggleFiltersBtn">
                                <i class="fa fa-times"></i>
                            </span>
                        </h3>
                        <contact-search-filter :purposes="{{$purposes}}" :campaigns="{{$campaigns}}" :folders="{{$tags}}" :events="{{$events}}" :permissions="{{$permissions}}" :groups="{{$groups}}" :custom_fields="{{ $customFields }}"></contact-search-filter>
                    </div>
                </div>
                <contact-search-add-remove-tags :folders="{{$tags}}"></contact-search-add-remove-tags>
            </div>
            
            @if (auth()->user()->can('transaction-view'))
            <div class="card-deck mb-3">
                <div class="card">
                    <div class="card-body p-0 d-flex align-items-center">
                        <i  class="fa fa-dollar p-4 font-2xl mr-3 bg-info"></i>
                        <div>
                            <div class="text-muted text-uppercase font-weight-bold small">
                                Total Amount
                            </div>
                            <div class="text-value-sm" id="totalAmount"></div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body p-0 d-flex align-items-center">
                        <i class="bg-success fa fa-dollar p-4 font-2xl mr-3"></i>
                        <div>
                            <div class="text-muted text-uppercase font-weight-bold small">
                                Lifetime Total
                            </div>
                            <div class="text-value-sm" id="lifetimeTotal"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            {!! $dataTable->table(['class'=>'table-striped w-100 nowrap']) !!}
        </div>
    </div>

    @include('datatables.includes.save-search-modal')
@endsection

@push('scripts')
    <script>
    var selectedState = '{{ array_get($selectedState, 'id') }}';
    </script>

    {{-- Not sure why I needed to explictly add these --}}
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    {{-- <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet"> --}}
    {{-- <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap.min.css" rel="stylesheet"> --}}
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    
    <script src="//cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    {{-- <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap.min.js"></script> --}}
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="//cdn.datatables.net/buttons/1.6.2/js/buttons.flash.min.js"></script>
    {{-- <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.bootstrap.min.js"></script> --}}
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.bootstrap4.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="//cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>
    <script src="//cdn.datatables.net/buttons/1.6.2/js/buttons.print.min.js "></script>
    <script src="/vendor/datatables/buttons.server-side.js?t={{ filemtime(public_path('vendor/datatables/buttons.server-side.js')) }}"></script>
    <script src="/vendor/datatables/custom-scripts.js?t={{ filemtime(public_path('vendor/datatables/custom-scripts.js')) }}"></script>
    
    <script src="//cdn.datatables.net/responsive/2.2.5/js/dataTables.responsive.min.js"></script>
    <script src="//cdn.datatables.net/buttons/1.6.2/js/buttons.colVis.min.js"></script>
    
    {{-- <link href="//cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css" rel="stylesheet"> --}}
    {{-- <link href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.bootstrap.min.css" rel="stylesheet"> --}}
    <link href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.bootstrap4.min.css" rel="stylesheet">
    {{-- <link href="//cdn.datatables.net/responsive/2.2.5/css/responsive.dataTables.min.css" rel="stylesheet"> --}}
    {{-- <link href="https://cdn.datatables.net/responsive/2.2.5/css/responsive.bootstrap.min.css" rel="stylesheet"> --}}
    <link href="https://cdn.datatables.net/responsive/2.2.5/css/responsive.bootstrap4.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="{{asset('css/custom_datatable.css')}}">
    
    <script>
    (function ($, DataTable) {
        // copied from public/vendor/datatables/buttons.server-side.js
        var _buildUrl = function (dt, action) {
            var url = dt.ajax.url() || '';
            var params = dt.ajax.params();
            params.action = action;
            
            if (url.indexOf('?') > -1) {
                return url + '&' + $.param(params);
            }
            
            return url + '?' + $.param(params);
        };
        
        DataTable.ext.buttons.add_remove_tag = {
            className: 'buttons-add_remove_tag',
            
            text: function (dt) {
                return '<i class="fa fa-plus"></i> ' + dt.i18n('buttons.add_remove_tag', 'Add or Remove Tags');
            },
            
            action: function (e, dt, button, config) {
                $('body').trigger('click')
            }
        };
        
    })(jQuery, jQuery.fn.dataTable);
    
    
    </script>
    {!! $dataTable->scripts() !!}
    
    <script>
    $('#customFilterButton').on('click', function (e) {
        window.LaravelDataTables['dataTableBuilder'].draw()
        e.preventDefault()
    })
    $('#searchFilters input, #searchFilters select').on('change', function (e) {
        window.LaravelDataTables['dataTableBuilder'].draw()
    })
    $('#searchFilters input').on('keyup', function (e) {
        window.LaravelDataTables['dataTableBuilder'].draw()
    })
    $(document).ready(function () {
        $('#dataTableBuilder')
            .on('processing.dt', function (e, settings, processing) {
                $('#overlay').removeClass('d-block');
            })
            .dataTable();
    })
    
    $('#searchLists').keyup(customDelay(function () {
        let search = $(this).val().toLowerCase();
        $('[aria-labelledby="savedSearchesDropdown"] [data-stateName]').each(function () {
            if ($(this).attr('data-stateName').toLowerCase().includes(search)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }, 500));
    
    function resetFilters() {
        $('.resetButton').trigger('click');
    }
    
    function datatableAction(action) {
        window.LaravelDataTables['dataTableBuilder'].button(action).trigger();
    }
    
    function deleteState() {
        confirmMessage('Alert', 'Are you sure you want to delete this list?', function () {
            customAjax({
                url: "/crm/search/contacts/state/"+selectedState,
                type: 'delete',
                success: function (data) {
                    Swal.fire('List was deleted successfully', '', 'success');
                    window.location.href = '{{ route('search.contacts') }}';
                }
            });
        });
    }
    
    @if (auth()->user()->can('transaction-view'))
    function loadTotals(table) {
        var firstRow = table.api().data()[0];
        
        if (firstRow) {
            $('#totalAmount').html(firstRow.total_sum);
            $('#lifetimeTotal').html(firstRow.lifetime_total_sum);
        } else {
            $('#totalAmount').html('$0.00');
            $('#lifetimeTotal').html('$0.00');
        }
    }
    @endif
    
    </script>
    <script src="{{asset('js/contact-search.js')}}"></script>
@endpush
