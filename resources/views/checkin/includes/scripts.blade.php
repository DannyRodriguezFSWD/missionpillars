@push('scripts')
<script>
    let group;
    let event;
    let print = false;
    let searchContact;
    let groupName;
    let eventName;
    let printSettings = {
        'checkin_print_info': 'name',
        'checkin_details_parent': 'parent',
        'print_tags': 2
    };
    let contactsToPrint;
    
    @isset ($selectedGroup)
        group = '{{ array_get($selectedGroup, 'uuid') }}'
        $('#groupsDropdown').html('<b>Group:</b> <i class="fa fa-filter"></i> {{ array_get($selectedGroup, 'name') }}');
        $('#groupNameHeader').html('<i class="fa fa-users fa-2x mt-2"></i> {{ array_get($selectedGroup, 'name') }}');
    @elseif ($groupUuid === '0')
        group = '{{ $groupUuid }}'
        $('#groupsDropdown').html('<b>Group:</b> <i class="fa fa-filter"></i> {{ __('All People') }}');
        $('#groupNameHeader').html('<i class="fa fa-users fa-2x mt-2"></i> {{ __('All People') }}');
    @endisset
    
    @isset ($selectedEvent)
        event = '{{ array_get($selectedEvent, 'uuid') }}'
        $('#eventsDropdown').html('<b>Event:</b> <i class="fa fa-filter"></i> {{ array_get($selectedEvent, 'template.name') }}');
        $('#eventNameHeader').html('<i class="fa fa-calendar fa-2x mt-2"></i> {{ array_get($selectedEvent, 'template.name') }}');
    @endisset
    
    filterContacts();
    
    $('[data-tooltip="tooltip"]').tooltip();
    
    $('#searchGroups').keyup(customDelay(function () {
        let search = $(this).val().toLowerCase();
        $('[aria-labelledby="groupsDropdown"] [data-name]').each(function () {
            if ($(this).attr('data-name').toLowerCase().includes(search)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }, 500));
    
    $('#searchEvents').keyup(customDelay(function () {
        let search = $(this).val().toLowerCase();
        $('[aria-labelledby="eventsDropdown"] [data-name]').each(function () {
            if ($(this).attr('data-name').toLowerCase().includes(search)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }, 500));
    
    $('#searchCheckinContacts').keyup(customDelay(function () {
        searchContact = $(this).val().toLowerCase();
        filterContacts();
    }, 500));
    
    function filterContacts(el, type) {
        if (el) {
            $(el).parents('.dropdown').find('button').html('<b>'+type[0].toUpperCase()+type.slice(1)+':</b> <i class="fa fa-filter"></i> '+$(el).attr('data-name'));
        
            if (type === 'group') {
                group = $(el).attr('data-id');
                groupName = $(el).attr('data-name');
            } else {
                event = $(el).attr('data-id');
                eventName = $(el).attr('data-name');
            }
        }
        
        if (event && !group) {
            $('[data-div="group-settings"]').removeClass('disabled btn-secondary');
            $('[data-div="group-settings"]').click();
        }
        
        if (group && event) {
            window.history.pushState(null, '', '{{ route('checkin.index') }}/'+group+'/'+event);
            
            $('[data-div="print-settings"]').removeClass('disabled btn-secondary');
            
            if (!print) {
                $('[data-div="print-settings"]').click();
            }
            
            print = true;
            
            $('#checkinAlert').hide();
            $('#eventFormContainer').fadeOut();
            $('#contactFormContainer').fadeOut();
            
            $('#addContactAlert').removeClass('d-none');
            
            customAjax({
                url: "{{ route('contacts.directory.search') }}",
                data: {
                    groupUuid: group,
                    event: event,
                    search: searchContact,
                    view: 'checkin'
                },
                success: function (data) {
                    $('[data-peopleList]').html('');

                    if (data.count > 0) {
                        $('[data-peopleList]').html(data.html);
                        $('#searchCheckinContacts').removeClass('d-none');
                        $('#printTags').removeClass('d-none');
                    } else {
                        $('[data-peopleList]').html('<div class="alert alert-info">There are no people that match your search.</div>');
                        if (!searchContact) {
                            $('#searchCheckinContacts').addClass('d-none');
                            $('#printTags').addClass('d-none');
                        }
                    }
                    
                    $('[name="last_page"]').val(data.lastPage);
                    $('[name="last_search"]').val(new Date().getTime());
                    
                    delete $('[data-peopleList="true"]').prevObject.scrollPaginate;
                    $('[data-peopleList="true"]').unbind('scroll');
                    
                    $('[data-peopleList="true"]').scrollPaginate({
                        scroll: $('[data-peopleList="true"]'),
                        url: '{{ route('contacts.directory.search') }}',
                        data: {
                            groupUuid: group,
                            event: event,
                            view: 'checkin'
                        },
                        search: $('#searchCheckinContacts'),
                        lastPage: data.lastPage
                    });
                    
                    if (groupName) {
                        $('#groupNameHeader').html('<i class="fa fa-users fa-2x mt-2"></i>' + groupName);
                    }
                    
                    if (eventName) {
                        $('#eventNameHeader').html('<i class="fa fa-calendar fa-2x mt-2"></i> ' + eventName);
                    }
                }
            });
        }
    }
    
    function selectContact(el) {
        if ($(el).hasClass('nocheck')) {
            return false;
        }
    
        let checkbox = $(el).find('.fa-square-o');
        let action = 'add';
        
        if (!checkbox.length) {
            action = 'checkout';
            checkbox = $(el).find('.fa-check-square');
        }
        
        if (!checkbox.length) {
            action = 'remove';
            checkbox = $(el).find('.fa-close');
        }
        
        if (checkbox.length) {
            customAjax({
                url: "{{ route('checkin.store') }}",
                data: {
                    contact: $(el).attr('data-contactId'),
                    event: event,
                    action: action
                },
                beforeSend: function () {
                    if (action === 'add') {
                        checkbox.removeClass('fa-square-o text-info').addClass('fa-refresh fa-spin text-warning');
                    } else if (action === 'checkout') {
                        checkbox.removeClass('fa-check-square text-success').addClass('fa-refresh fa-spin text-warning');
                    } else {
                        checkbox.removeClass('fa-close text-muted').addClass('fa-refresh fa-spin text-warning');
                    }
                },
                success: function (data) {
                    checkbox.parents('.d-table').find('.checkinTime').remove();
                    
                    if (action === 'add') {
                        checkbox.removeClass('fa-refresh fa-spin text-warning').addClass('fa-check-square text-success');
                        checkbox.parents('.d-table').find('.d-table-cell:eq(2)').append(data.time);
                    } else if (action === 'checkout') {
                        checkbox.removeClass('fa-refresh fa-spin text-warning').addClass('fa-close text-muted');
                        checkbox.parents('.d-table').find('.d-table-cell:eq(2)').append(data.time);
                    } else {
                        checkbox.removeClass('fa-refresh fa-spin text-warning').addClass('fa-square-o text-info');
                    }
                },
                error: function () {
                    if (action === 'add') {
                        checkbox.removeClass('fa-refresh fa-spin text-warning').addClass('fa-square-o text-info');
                    } else if (action === 'checkout') {
                        checkbox.removeClass('fa-refresh fa-spin text-warning').addClass('fa-check-square text-success');
                    } else {
                        checkbox.removeClass('fa-refresh fa-spin text-warning').addClass('fa-close text-muted');
                    }
                }
            });
        }
    }
    
    function saveEvent(el) {
        customAjax({
            url: $(el).parents('form').attr('action'),
            data: {
                name: $('#eventForm input[name="name"]').val(),
                contact_id: {{ auth()->user()->contact->id }},
                content: $('#eventForm textarea[name="description"]').val(),
                start_date: $('#eventForm input[name="start_date"]').val(),
                start_time: $('#eventForm select[name="start_time"]').val(),
                end_date: $('#eventForm input[name="start_date"]').val(),
                end_time: $('#eventForm select[name="start_time"]').val(),
                calendar_id: {{ array_get(auth()->user()->tenant->calendars, '0.id') }}
            },
            success: function (response) {
                if (response.id) {
                    dispatchToast('SUCCESS', '{{ __('Event created successfully') }}')
                    
                    event = response.uuid;
                    $('#eventsDropdown').html('<b>Event:</b> <i class="fa fa-filter"></i> '+$('#eventForm input[name="name"]').val());
                    $('#eventNameHeader').html('<i class="fa fa-calendar fa-2x mt-2"></i> '+$('#eventForm input[name="name"]').val());
                    isLoading = false;
                    filterContacts();
                    
                    $('#eventForm input[name="name"]').val('');
                    $('#eventForm textarea[name="description"]').val('');
                }
            },
            error: function (e) {
                if (e.responseJSON) {
                    Swal.fire('Validation Error', parseResponseJSON(e.responseJSON), 'error');
                }
            }
        });
    }
    
    $('[name="checkin_print_info"]').change(function () {
        if ($(this).val() === 'name_and_details') {
            $('#printDetialsContainer').fadeIn();
        } else {
            $('#printDetialsContainer').fadeOut();
        }
    });
    
    $('.promote_togglable_div').hide();
    $('.promote_togglable_div').each(function () {
        this.querySelector('.card-header').insertAdjacentHTML('afterbegin',"<button class=\"close\" onclick=\"closeToggleableDiv($(this).parents('.promote_togglable_div'))\">&times;</button>")
    });
    function closeToggleableDiv(jEl){
        $('#toggleable-title').slideDown();
        jEl.slideUp();
    }
    $('.promote_div_toggler').on('click',function (){
        if (!$(this).hasClass('disabled')) {
            $('.promote_div_toggler').removeClass('active');
            $(this).addClass('active');
            $('.promote_togglable_div:visible').slideUp();
            if ($('.promote_togglable_div:visible').length){
                var el = this;
                setTimeout(function () {
                    $(document.getElementById(el.getAttribute('data-div'))).slideDown();
                    $('#toggleable-title').slideUp();
                },300);
            }else {
                $(document.getElementById(this.getAttribute('data-div'))).slideDown();
                $('#toggleable-title').slideUp();
            }
        }
    })
    
    $('#print-settings input').change(function () {
        let name = $(this).attr('name');
        let value = $(this).attr('value');
        let type = $(this).attr('type');
        let checked = $(this).prop('checked');

        if (type === 'checkbox' && !checked) {
            printSettings[name] = null;
        } else {
            printSettings[name] = value;
        }

        checkinPrintPreview();
    });
    
    loadCheckinSettings();
    
    function loadCheckinSettings() {
        if (!group) {
            $('[data-div="event-settings"]').click();
        }
        
        if (event && group) {
            $('[data-div="group-settings"], [data-div="print-settings"]').removeClass('disabled btn-secondary');
        }
        
        let printSettingsCookie = mpGetCookie('mpCheckinPrintSettings');
        
        if (printSettingsCookie) {
            printSettings = JSON.parse(atob(printSettingsCookie));
        }
        
        for (let info in printSettings) {
            if (printSettings[info]) {
                $('[name="'+info+'"][value="'+printSettings[info]+'"]').click();
            }
        }
        
        checkinPrintPreview();
    }
    
    function savePrintSettings() {
        $('#checkinSettingsModal').modal('hide');
        
        let data = btoa(JSON.stringify(printSettings));
        
        mpSetCookie('mpCheckinPrintSettings', data, 30);
    }
    
    function checkinPrintPreview() {
        $('[data-print-info]').hide();
        
        for (let info in printSettings) {
            if (printSettings[info]) {
                $('[data-print-info="'+info+'"][data-value="'+printSettings[info]+'"]').show();
            }
        }
    }
    
    function showPrintTags() {
        $('#checkinPrintModal').modal('show');
        $('#searchContacts').keyup();
    }
    
    $('#searchContacts').keyup(customDelay(function () {
        let search = $(this).val();

        customAjax({
            url: "{{ route('contacts.directory.search') }}",
            data: {
                search: search,
                view: 'checkinPrint',
                event: event,
                groupUuid: group
            },
            success: function (data) {
                $('#contactsTable tbody').html('');

                if (data.count > 0) {
                    $('#contactsTable tbody').html(data.html);
                } else {
                    $('#contactsTable tbody').html('<tr><td colspan="7"><div class="alert alert-info">@lang('There are no people that matched your search').</div></td></tr>');
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
    
    function printTags() {
        customAjax({
            url: "{{ route('checkin.print') }}",
            data: {
                search: $('#searchContacts').val(),
                event: event,
                group: group
            },
            success: function (data) {
                $('#searchCheckinContacts').keyup();
                
                $('#printContainer').html('');

                var datetimestring = new Date().toLocaleDateString('en-us', {weekday: 'long', month: 'long', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit'})

                $('.checkinPrintSelector').each(function () {
                    if ($(this).prop('checked')) {
                        let row = $(this).parents('tr');

                        $('#printTemplate').find('.date').html(datetimestring)
                        $('#printTemplate').find('.name').html(row.find('.checkin_details_name').html());

                        if (printSettings['checkin_print_info'] === 'name_and_details') {
                            for (let info in printSettings) {
                                if (info.substring(0, 15) === 'checkin_details' && printSettings[info]) {
                                    let detail = row.find('.'+info).html();
                                    let detailPrepend = row.find('.'+info).data('prepend');
                                    let detailString = '<p class="printDetails">' + (detailPrepend ? detailPrepend + ": " : "") + detail + '</p>';

                                    if (detail) {
                                        $('#printTemplate').find('.date').before(detailString);
                                    }
                                }
                            }
                        }

                        $('#printContainer').append($('#printTemplate').html());

                        if (printSettings['print_tags'] == 2) {
                            $('#printContainer').append($('#printTemplate').html());
                        }

                        $('#printTemplate .printDetails').remove();
                    }
                });

                window.print();
            }
        });
    }
    
    function reprintTag(el, contactId) {
        customAjax({
            url: "{{ route('checkin.reprint') }}",
            data: {
                contactId: contactId,
                event: event
            },
            success: function (data) {
                $(el).remove();
                Swal.fire('Contact was marked for reprint', 'Click on "Print Tags" to print', 'success');
            }
        });
    }
</script>

<script src="{{ asset('js/people-search-with-create.js') }}"></script>
<script src="{{ asset('js/family-search-with-create.js') }}"></script>
@endpush
