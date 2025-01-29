@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('events.checkin', $event) !!}
@endsection

@section('content')



        @if( isset($tags) )
        <input type="hidden" name="url" value="{!! route('mobile.contacts.search', ['id' => $split->id, 'tags' => $tags]) !!}"/>
        @elseif( isset($forms) )
        <input type="hidden" name="url" value="{!! route('mobile.contacts.search', ['id' => $split->id, 'forms' => $forms]) !!}"/>
        @else
        <input type="hidden" name="url" value="{!! route('mobile.contacts.search', ['id' => $split->id]) !!}"/>
        @endif

        <div class="row">
            <div class="col-md-1"></div>
            @include('events.includes.event-settings-menu')

            <div class="col-md-8 vertical-menu-bar mt-2 mt-md-0">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-6 mb-4 mb-xl-0">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    </div>
                                    <input type="text" name="search" id="autocomplete" class="form-control" placeholder="Search">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" id="clear">
                                            <i class="fa fa-close"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="pull-right">
                                    <a class="btn btn-primary btn-block-xs mb-2 mb-xs-0" href="{{ route('events.tickets.export', ['id' => $split->id]) }}">
                                        <span class="fa fa-file-excel-o"></span> @lang('Export')
                                    </a>

                                    <button class="btn btn-primary btn-block-xs mb-2 mb-xs-0" onclick="goToAdvancedContactSearch('create a communication')">
                                        <i class="fa fa-envelope"></i> @lang('Create Communication')
                                    </button>

                                    <button class="btn btn-primary btn-block-xs mb-2 mb-xs-0" onclick="goToAdvancedContactSearch('send an SMS')">
                                        <span class="fa fa-comment"></span> @lang('Send SMS')
                                    </button>
                                    
                                    <button class="btn btn-primary btn-block-xs mb-2 mb-xs-0" data-toggle="modal" data-target="#checkinReportModal">
                                        <span class="fa fa-file-pdf-o"></span> @lang('Report')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex pull-right">
                            <span class="mr-2">
                                Show Released Tickets 
                                <i class="fa fa-question-circle-o text-info cursor-pointer" data-toggle="tooltip" title="Show/hide released tickets. Released tickets are tickets that have not been claimed by people. These happen in case someone starts the registration process but does not finish it. Tickets will be released 10 minutes after the registration process has started."></i>
                            </span>
                            <label class="c-switch c-switch-label c-switch-success">
                                <input type="checkbox" onchange="toggleReleased();" class="c-switch-input">
                                <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                            </label>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped" data-table="check-in">
                                <thead>
                                <tr>
                                    <th>@lang('Contact')</th>
                                    @if(array_get($event,'ask_whose_ticket'))<th>@lang('For')</th>@endif
                                    <th>@lang('Ticket Nr')</th>
                                    <th>@lang('Ticket')</th>
                                    @if($show_paid_column)
                                        <th>@lang('Paid')</th>
                                    @endif
                                    @if(array_get($event, 'form_id') > 1)
                                        <th>@lang('Form Filled')</th>
                                    @endif
                                    <th class="text-right">Check In</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($registries as $registry)
                                    @if( count(array_get($registry, 'tickets')) > 0 )
                                        @include('events.includes.checkin.with-tickets')
                                    @else
                                        @include('events.includes.checkin.without-tickets')
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<div id="overlay">
    <div class="spinner">
        <div class="rect1"></div>
        <div class="rect2"></div>
        <div class="rect3"></div>
        <div class="rect4"></div>
        <div class="rect5"></div>
        <!-- <p>Wait a moment please</p> -->
    </div>
</div>

<div id="top" style="z-index: 2;">
    <button class="btn btn-primary btn-lg">
        <span class="icon icon-arrow-up"></span>
    </button>
</div>

@include('events.includes.actions-event-modal')
@include('events.includes.checkin.modals.report')
@include('events.includes.checkin.scripts')

<script type="text/javascript">
function goToAdvancedContactSearch(action) {
    let data = {
        name: "event_saveSearch_"+{{ $event->id }},
        search: {
            event_registration: [''+{{ $event->id }}]
        }
    }
    axios.post('{{ route('search.contacts.state.store') }}', data)
    .then((response)=>{
        // console.log(response);
        let state = response.data.state;

        Swal.fire({
            title: "Advanced Contact Search",
            text: 'The attendees will automatically be selected on the next screen. From there you will be able to '+action+' from the Actions menu.',
            type: 'info',
            timer: 5000,
            showCancelButton: true
        }).then(function (res) {
            console.log('here');
            location.href = '{{ route('search.contacts.state.show', 99999999) }}'.replace(99999999, state.id)
            if (res.value) {
            }
        });
    })
}
</script>

@push('scripts')
<script>
function toggleReleased() {
    $('[data-releaed="true"]').toggleClass('d-none');
}

$('[data-table="check-in"] [data-delete-ticket="true"]').click(function () {
    let $this = $(this);
    Swal.fire({
        title: 'Do you want to delete this ticket?',
        text: 'This action cannot be undone.',
        type: 'question',
        showCancelButton: true
    }).then(function (res) {
        customAjax({
            url: '/crm/events/checkin/delete-ticket/'+$this.attr('data-ticket-id'),
            data: null,
            success: function (response) {
                if (response.success) {
                    $this.parents('tr').remove();
                    Swal.fire('Ticket deleted successfully', '', 'success');
                } else {
                    Swal.fire('An error occurred please try again later', '', 'error');
                }
            }
        });
    });
});
</script>
@endpush
@endsection
