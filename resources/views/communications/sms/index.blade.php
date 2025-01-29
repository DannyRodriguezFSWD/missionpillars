@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('sms.index') !!}
@endsection
@section('content')

@push('styles')
<link href="{{ asset('css/sms.timeline.custom.css') }}?t={{ time() }}" rel="stylesheet">
@endpush

<div class="row bg-white" style="height: calc(100vh - 153px);">
    <div class="col-10 offset-2 d-none" id="smsActions">
        <div class="bg-light position-absolute w-100 py-2" style="top: 0; left: 0;z-index: 5;">
            <button class="btn btn-light border-0 mr-2" onclick="hideSmsActions();">
                <i class="fa fa-times fa-2x tex-muted"></i>
            </button>
            
            <button class="btn btn-secondary mr-2" onclick="markSmsAsReadOrUnread('unread');">
                Mark as Unread
            </button>
            
            <button class="btn btn-secondary mr-2" onclick="markSmsAsReadOrUnread('read');">
                Mark as Read
            </button>
            
            <button class="btn btn-secondary" onclick="selectAllSms();">
                Select All
            </button>
        </div>
    </div>
    
    <div class="col-12 d-md-none">
        <div class="position-absolute mt-5 d-none" style="top: 0; left: 0;z-index: 5;" id="backToPreview">
            <button class="btn btn-secondary" onclick="loadSmsPreview();">
                <i class="fa fa-caret-left"></i> Back
            </button>
        </div>
    </div>
    
    <div class="col-md-2 border-right">
        <div class="d-none d-md-block">
            <a href="{{ route('sms.create') }}" class="btn btn-success btn-block my-3">
                <i class="fa fa-commenting-o"></i> @lang('Compose')
            </a>
            <ul class="list-group" data-smsSidebarList="true">
                <li class="list-group-item list-group-item-action border-0 h5 text-dark cursor-pointer active" data-status="received" onclick="loadSmsPreview('received');">
                    <i class="fa fa-inbox"></i> @lang('Inbox')
                </li>
                <li class="list-group-item list-group-item-action border-0 h5 text-dark cursor-pointer" data-status="sent" onclick="loadSmsPreview('sent');">
                    <i class="fa fa-paper-plane"></i> @lang('Sent')
                </li>
                <li class="list-group-item list-group-item-action border-0 h5 text-dark cursor-pointer" data-status="scheduled" onclick="loadSmsPreview('scheduled');">
                    <i class="fa fa-clock-o"></i> @lang('Scheduled')
                </li>
            </ul>
        </div>
        
        <div class="d-md-none">
            <ul class="list-group list-group-horizontal" data-smsSidebarList="true">
                <li class="list-group-item list-group-item-action border-0">
                    <a href="{{ route('sms.create') }}" class="text-dark">
                        <i class="fa fa-commenting-o"></i> @lang('Compose')
                    </a>
                </li>
                <li class="list-group-item list-group-item-action border-0 text-dark cursor-pointer active" data-status="received" onclick="loadSmsPreview('received');">
                    <i class="fa fa-inbox"></i> @lang('Inbox')
                </li>
                <li class="list-group-item list-group-item-action border-0 text-dark cursor-pointer" data-status="sent" onclick="loadSmsPreview('sent');">
                    <i class="fa fa-paper-plane"></i> @lang('Sent')
                </li>
            </ul>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-4 border-right" id="previewContainer">
        <div class="input-group my-3">
            <input type="text" class="form-control bg-light" placeholder="Search" aria-label="Search" id="searchText">
            <div class="input-group-append">
                <span class="input-group-text">
                    <i class="fa fa-search"></i>
                </span>
            </div>
        </div>
        
        <div class="dropdown mb-3">
            <button class="btn btn-light dropdown-toggle" type="button" id="smsFilters" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Filters
            </button>
            <div class="dropdown-menu" aria-labelledby="smsFilters">
                <form id="smsFilterForm" class="px-4 py-3">
                    <p class="mb-1 h5">Show:</p>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="read" id="read1" value="all" checked>
                        <label class="form-check-label" for="read1">@lang('All')</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="read" id="read2" value="unread">
                        <label class="form-check-label" for="read2">@lang('Unread')</label>
                    </div>
                    <div class="dropdown-divider"></div>
                    <p class="mb-1 h5">@lang('Phone Number'):</p>
                    @foreach ($smsPhoneNumbers as $i => $phone)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ array_get($phone, 'id') }}" id="smsPhoneNumber{{ $i }}" checked>
                        <label class="form-check-label" for="smsPhoneNumber{{ $i }}">
                            {{ array_get($phone, 'name_and_number') }}
                        </label>
                    </div>
                    @endforeach
                    <div class="dropdown-divider"></div>
                    <button type="submit" class="btn btn-primary btn-block">@lang('Save')</button>
                </form>
            </div>
        </div>

        <div id="textConversationPreview" class="row overflow-auto" style="height: calc(100vh - 271px);">
            <div class="col-12" id="textConversationPreviewContainer"></div>
        </div>
    </div>
    
    <div class="col-lg-7 col-md-6 d-none d-md-block" id="textsContainer">
        <div id="textConversationEmpty" class="text-center m-auto d-table h-100">
            <i class="fa fa-commenting-o fa-4x text-muted d-table-cell align-middle"></i>
        </div>

        <div id="textConversationFull" class="d-none"></div>
    </div>
</div>

@push('scripts')
<script>
    let smsStatus = 'received';
    
    function loadSmsPreview(status) {
        if (status) {
            $('[data-smsSidebarList="true"] li').removeClass('active');
            $('[data-smsSidebarList="true"] li[data-status="'+status+'"').addClass('active');
            smsStatus = status;
            $('#searchText').val('');
        }
        
        let url = '{{ route('sms.texts-preview') }}';

        if (smsStatus === 'scheduled') {
            url = '{{ route('sms.texts-scheduled') }}'
        } 

        customAjax({
            url: url,
            data: {
                status: smsStatus,
                search: $('#searchText').val(),
                read: $('[name="read"]:checked').val(),
                smsPhoneNumbers: getSelectedSmsPhoneNumbers()
            },
            success: function (response) {
                $('#textConversationPreview .col-12').html(response.html);
                $('#textConversationFull').addClass('d-none');
                $('#textConversationEmpty').addClass('d-table').removeClass('d-none');
                
                $('#textsContainer').addClass('d-none d-md-block');
                $('#backToPreview').addClass('d-none');
                $('#previewContainer').removeClass('d-none d-md-block');
                
                $('#textConversationPreview').find('[name="last_page"]').val(response.lastPage);
                $('#textConversationPreview').find('[name="last_search"]').val(new Date().getTime());
            }
        });
    }
    
    function loadTexts(el, contact) {
        customAjax({
            url: '{{ route('sms.texts') }}',
            data: {
                contact: contact,
                smsPhoneNumbers: getSelectedSmsPhoneNumbers()
            },
            success: function (response) {
                $('#textConversationEmpty').addClass('d-none').removeClass('d-table');
                $('#textConversationFull').html(response.html);
                $('#textConversationFull').removeClass('d-none');
                
                $('#previewContainer').addClass('d-none d-md-block');
                $('#textsContainer').removeClass('d-none d-md-block');
                $('#backToPreview').removeClass('d-none');
                
                var container = $('#textConversationFull').find('.row.overflow-auto');
                container.scrollTop(container.prop('scrollHeight'));
                
                $(el).find('p.text-info').removeClass('text-info font-weight-bold').addClass('text-muted');
                $(el).find('i.fa-circle').remove();
                
                $('#textConversationPreview').find('.d-table.bg-secondary').removeClass('bg-secondary');
                $(el).parent().addClass('bg-secondary');
                
                $('#textsThread').scrollPaginate({
                    scroll: $('#textsThread').parents('.overflow-auto'),
                    scrollDirection: 'top',
                    url: '{{ route('sms.texts') }}',
                    data: {
                        contact: contact,
                        smsPhoneNumbers: getSelectedSmsPhoneNumbers(),
                        loadMoreTexts: true
                    },
                    lastPage: response.lastPage
                });
            }
        });
    }
    
    function getSelectedSmsPhoneNumbers() {
        let numbers = [];
        
        $('[id*=smsPhoneNumber]:checked').each(function () {
            numbers.push(parseInt($(this).val()));
        });
        
        return numbers;
    }
    
    function getSelectedSms() {
        let sms = [];
        
        $('[id*=smsSentAction]:checked').each(function () {
            sms.push(parseInt($(this).val()));
        });
        
        return sms;
    }
    
    function showSmsActions() {
        let sms = getSelectedSms();
        
        if (sms.length === 0) {
            $('#smsActions').addClass('d-none');
        } else {
            $('#smsActions').removeClass('d-none');
        }
    }
    
    function hideSmsActions() {
        $('[id*=smsSentAction]:checked').prop('checked', false);
        $('#smsActions').addClass('d-none');
    }
    
    function selectAllSms() {
        $('[id*=smsSentAction]').prop('checked', true);
    }
    
    function markSmsAsReadOrUnread(action) {
        customAjax({
            url: '{{ route('sms.mark') }}',
            data: {
                read: action === 'read' ? 1 : 0,
                sms: getSelectedSms()
            },
            success: function () {
                isLoading = false;
                loadSmsPreview();
                hideSmsActions();
            }
        });
    }
    
    function sendText() {
        if ($('#sms_phone_number_id').val().length === 0) {
            Swal.fire('Error', 'Please select a phone number', 'error');
            return false
        }
        
        if ($('[name="content"]').val().length === 0) {
            Swal.fire('Error', 'Please write a message', 'error');
            return false
        }
        
        customAjax({
            url: $('#sendTextForm').attr('action'),
            data: {
                'sms_phone_number_id': $('#sms_phone_number_id').val(),
                'content': $('[name="content"]').val(),
            },
            success: function (response) {
                Swal.fire('Success', 'Your text has been queued and will be sent shortly', 'success');
                isLoading = false;
                loadTexts(null, response.contact);
            }
        });
    }
   
    $('#searchText').keyup(customDelay(function () {
        loadSmsPreview();
    }, 500));

    $('#smsFilterForm').submit(function (e) {
        e.preventDefault();
        $('#smsFilters').dropdown('toggle');
        loadSmsPreview();
    });

    $(document).ready(function () {
        loadSmsPreview('received');
    });
    
    $('#textConversationPreviewContainer').scrollPaginate({
        scroll: $('#textConversationPreview'),
        url: '{{ route('sms.texts-preview') }}',
        data: {
            status: function () {
                return smsStatus;
            },
            read: function () {
                return $('[name="read"]:checked').val();
            },
            smsPhoneNumbers: function () {
                return getSelectedSmsPhoneNumbers();
            }
        },
        search: $('#searchText')
    });
    
    function loadSchedule(el, sms) {
        customAjax({
            url: '{{ route('sms.view-schedule') }}',
            data: {
                sms: sms
            },
            success: function (response) {
                $('#textConversationEmpty').addClass('d-none').removeClass('d-table');
                $('#textConversationFull').html(response.html);
                $('#textConversationFull').removeClass('d-none');
                
                $('#previewContainer').addClass('d-none d-md-block');
                $('#textsContainer').removeClass('d-none d-md-block');
                $('#backToPreview').removeClass('d-none');
                
                $('#textConversationPreview').find('.d-table.bg-secondary').removeClass('bg-secondary');
                $(el).parent().addClass('bg-secondary');
            }
        });
    }
    
    function cancelSchedule(el) {
        let url = $(el).data('url');
                
        Swal.fire({
            title: 'Are you sure you want to cancel this scheduled text?',
            type: 'question',
            showCancelButton: true
        }).then(res => {
            if (res.value){
                customAjax({
                    url: url,
                    type: 'DELETE',
                    success: function (response) {
                        Swal.fire('Scheduled SMS was canceled', '', 'success');
                        isLoading = false;
                        loadSmsPreview('scheduled');
                    }
                });
            }
        });
    }
</script>
@endpush

@endsection
