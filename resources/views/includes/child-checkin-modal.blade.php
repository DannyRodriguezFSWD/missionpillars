

<div class="modal fade" id="child-checkin-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title">@lang('Child Checkin URL')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    {{ Form::text('check-in-url', route('child-checkin.index'), ['class' => 'form-control', 'readonly' => true, 'id' => 'check-in-url']) }}
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" style="width: 100px;" onclick="copy('check-in-url')">
                            <i class="fa fa-copy"></i>
                            Copy&nbsp;
                        </button>
                        <a href="{{ route('child-checkin.index') }}" target="_blank" class="btn btn-primary" style="width: 100px;">
                            <i class="fa fa-external-link"></i>
                            Open
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')
    
@endpush
