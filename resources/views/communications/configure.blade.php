@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('communications.configure', $communication) !!}
@endsection

@section('title')
    Configure Communication
@endsection

@section('content')

@if ($totalEmailsScheduled > 0) 
<div class="alert alert-warning">
    There are <b>{{ $totalEmailsScheduled }} emails</b> scheduled for 
    <b>{{ displayLocalDateTime(array_get($communication, 'time_scheduled'))->format('D, M j g:i A') }}</b>. 
    You will not be able to update or resend this communication until these email are sent.
</div>

<a href="{{ route('communications.emailsummary',$communication->id) }}" class="btn btn-primary">View Email Summary</a>
<button class="btn btn-danger" onclick="cancelSend('{{ route('communications.cancel-send', $communication->id) }}');">
    Cancel Send/Edit
</button>

@push('scripts')
<script>
    function cancelSend(url) {
        customAjax({
            url: url,
            success: function (response) {
                if (response.success) {
                    Swal.fire('Scheduled emails have been canceled', '', 'success');
                    window.location.reload();
                }
            }
        });
    }
</script>
@endpush

@else
@include('emails.includes.functions')
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
<style media="screen">
.tab-button {
  /* padding: 6px 10px; */
  padding: 6px 0;
  /* border-top-left-radius: 3px;
  border-top-right-radius: 3px;
  border: 1px solid #ccc; */
  /* cursor: pointer; */
  /* background: #f0f0f0; */
  margin-bottom: -1px;
  margin-right: -1px;
  text-transform: capitalize;
}
.tab-button a,
.tab-button span{
  white-space: nowrap;
}
.tab-button:hover {
  /* background: #e0e0e0; */
}
.tab-button.active {
  /* background: #e0e0e0; */
  font-weight: bold;
}
.tab {
  border: 1px solid #ccc;
  padding: 10px;
}
</style>
@endpush

<div id="configurecommunications" class="card">
    <div class="card-body">
        {{ Form::open(['route' => ['communications.update', $communication->id], 'autocomplete' => 'off']) }}
        {{ method_field('PUT') }}
        {{ Form::hidden('uid', Crypt::encrypt($communication->id)) }}
        {{ Form::hidden('redirect_route_name', 'communications.printfinish',["v-if"=>"isPrint"]) }}
        {{ Form::hidden('redirect_route_name', 'communications.emailfinish',["v-if"=>"isEmail"]) }}

        {{-- <input type="text" v-model="currentTab"> --}}
        <span class="tab-button"> <a href="{{route('communications.index')}}">Mass Email/Print</a> </span>
        &gt; <span class="tab-button"> <a href="{{route('communications.edit', $communication->id)}}">Edit Communication</a> </span>
        <span v-for="tab in tabs" v-bind:key="tab" v-bind:class="['tab-button', { active: currentTab === tab }]">
            &gt;
            <template v-if="hideLink(tab)">
                <span>@{{ tabLabels[tab] }}</span>
            </template>
            <template v-else>
                <a href="#" @click="changeTab(tab)">@{{ tabLabels[tab] }}</a>
            </template>
        </span>
        <div class="row mt-3">
            <div class="col-md-12">
                <h3>{{ (strstr($currentTab, 'print') ? 'Configure Print': 'Configure Email' ?:'') }}</h3>
                <hr>
            </div>
        </div>
        <keep-alive>
            <component v-bind:is="currentComponent"
            update_communication_route="{{route('communications.update',$communication->id)}}"
            view_email_route="{{route("communications.emailsummary",$communication->id)}}"
            view_pdf_route="{{route("print-mail.preview",$communication->id)}}"
            view_print_route="{{route("communications.printsummary",$communication->id)}}"
            configure_email_route="{{route("communications.configureemail",$communication->id)}}"
            configure_print_route="{{route("communications.configureprint",$communication->id)}}"
            email_summary_route="{{route("ajax.communications.emailsummary",$communication->id)}}"
            print_summary_route="{{route("ajax.communications.printsummary",$communication->id)}}"
            send_email_route="{{route("ajax.communications.sendemail",$communication->id)}}"
            track_print_route="{{route("ajax.communications.trackprint",$communication->id)}}"
            saved_search_route="{{route('search.contacts.state.index')}}"
            @configuration-saved="onConfigSaved" @communication-finished="onConfigConfirmed"></component>

        </keep-alive>

        {{ Form::close() }}
    </div>

    <div class="card-footer">&nbsp;</div>

    <loading v-if="$store.getters.getIsLoadingState"></loading>
</div>
@push('scripts')
<style>
    a.selected{
        background-color: #0074D9 !important;
        color: #fff;
    }
    a.selected:hover{
        color: #fff;
    }
    li input + ol{
        background: url("{{ asset('css/toggle-small-expand.png') }}") 44px 0px no-repeat;
    }
    li input:checked + ol{
        background: url("{{ asset('css/toggle-small.png') }}") 44px 4px no-repeat;
    }
</style>
@endpush

@push('scripts')
<script type="text/javascript">
    var defaultTab = '{{ $currentTab }}'
    var communication = <?php echo json_encode($communication); ?>;
    var tabLinks = {
        email: '{{ route('communications.configureemail',$communication->id) }}',
        'confirm-email': '{{ route('communications.configureemail',$communication->id, 'confirm') }}',
        'finish-email': '{{ route('communications.configureemail',$communication->id,'finish') }}',
        print: '{{ route('communications.configureprint',$communication->id) }}',
        'confirm-print': '{{ route('communications.configureprint',$communication->id, 'confirm') }}',
        'finish-print': '{{ route('communications.configureprint',$communication->id, 'finish') }}',
    }
</script>
<script type="text/javascript" src={{ asset('js/crm-communications-configure.js') }}>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    (function () {
        $(window).scroll(function () {
            var y = $(this).scrollTop();

            var button = $('#floating-buttons');
            if (y >= top) {
                button.css({
                    'position': 'fixed',
                    'top': '60px',
                    'right': '51px',
                    'z-index': '99'
                });
            } else {
                button.removeAttr('style')
            }
        });
    })();
</script>
@endpush
@endif

@endsection
