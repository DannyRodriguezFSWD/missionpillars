<div class="modal fade" id="actions-event-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-primary" role="document">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
            @if(!isset($split))
                <div class="row text-center">
                    <div class="col-sm-5 mb-2 mb-sm-0">
                        <a id="btn-check-in" href="" class="btn btn-info btn-lg">
                            <span class="icon icon-chart"></span> <span class="text-nowrap">@lang('Overview/Check In')</span>
                        </a>
                    </div>
                    <div class="col-sm-3 mb-2 mb-sm-0">
                        <a id="btn-settings" href="" class="btn btn-success btn-lg">
                            <span class="icon icon-settings"></span> @lang('Settings')
                        </a>
                    </div>
<!--                    <div class="col-sm-3">
                        <a id="btn-check-in" href="" class="btn btn-primary btn-lg">
                            <span class="icon icon-check"></span> @lang('Check In')
                        </a>
                    </div>-->
                    <div class="col-sm-3 mb-2 mb-sm-0">
                        {{ Form::open(['route' => ['events.destroy', ':id:'], 'method' => 'DELETE', 'id' => 'form']) }}
                        {{ Form::hidden('uid', ':uid:') }}
                        {{ Form::hidden('url', route('events.destroy', ['id' => ':id:'])) }}
                        <button type="submit" id="btn-delete" class="btn btn-danger btn-lg">
                            <span class="fa fa-trash"></span> @lang('Delete')
                        </button>
                        {{ Form::close() }}
                    </div>
                </div>
                <br>
            @endif
                <div class="row">
                    <div class="col-sm-12">
                        @include('events.includes.fragments.share-event-link')
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

<div id="overlay">
    <div class="spinner">
        <div class="rect1"></div>
        <div class="rect2"></div>
        <div class="rect3"></div>
        <div class="rect4"></div>
        <div class="rect5"></div>
        <!-- <p>@lang('Wait a moment please')</p> -->
    </div>
</div>

@push('scripts')
    @if(!isset($split))
    <script type="text/javascript">
        (function () {
            $('#btn-delete').on('click', function(e){
                var msg = "@lang('Are you sure you want to delete this event?')";
                let submit = false;
                let res = Swal.fire({
                    title: 'Are you sure?',
                    text: msg,
                    type: 'question',
                    showCancelButton: true
                }).then(res =>{
                    if (res.value) $('#form').submit();
                })
                return false
            });
        })();
    </script>
    @endisset
@endpush
