@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush
@include('events.includes.functions')

<div class="modal fade" id="new-event-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-primary" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'events.store']) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('New Event')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body event">
                <div class="row">
                    <div class="col-sm-6">
                        @include('events.includes.new-event-left')
                    </div>
                    <div class="col-sm-6">
                        @include('events.includes.new-event-right')
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('Create Event')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
            {{ Form::close() }}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


