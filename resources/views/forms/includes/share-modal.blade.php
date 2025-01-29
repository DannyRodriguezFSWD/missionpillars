<?php if ($form && $form->id): ?>
<div class="modal fade" id="share-modal-{{ $form->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Share Form')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="">Form Address</label>
                    <div class="input-group mb-3">
                        <input class="form-control" type="text" value="{{ route('forms.share', ['id' => $form->uuid]) }}" readonly/>
                        <div class="input-group-append">
                            <button class="btn btn-primary mt-0" onclick="copy_link(this,'Form Address','Form Address Copied to Clipboard')" type="button">Copy <i class="fa fa-copy"></i></button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>@lang('Link')</label>
                    <div class="input-group">
                        <textarea readonly class="form-control">Go to <a href="{{ route('forms.share', ['id' => $form->uuid]) }}">{{ $form->name }}</a></textarea>
                        <button class="btn btn-primary mt-0" onclick="copy_link(this,'Anchor Link','Link Copied to Clipboard',true)" type="button">Copy <i class="fa fa-copy"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label>@lang('Embed')</label>
                    <div class="input-group">
                        <textarea readonly class="form-control"><script src="{{ route('forms.iframe', ['id' => $form->uuid]) }}"></script></textarea>
                        <button class="btn btn-primary mt-0" onclick="copy_link(this,'Embed Link','Embed Copied to Clipboard',true)" type="button">Copy <i class="fa fa-copy"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <p class="mb-0">@lang('QR-Code')</p>
                    <p>
                        <img src="{{ sprintf(env('QRCODE'), route('forms.share', ['id' => $form->uuid])) }}"/>
                        <button type="button" class="btn btn-primary" onclick="downloadImage('{{ sprintf(env('QRCODE'), route('forms.share', ['id' => $form->uuid])) }}')">Download <i class="fa fa-download"></i></button>
                    </p>
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
@push('styles')
    <style>
        .modal label{
            text-align: left !important;
            float: left;
        }
    </style>
@endpush
@push('scripts')
    <script>
        let copy_link = (el,title,text,is_text_area) => {
            if (!is_text_area){
                el.parentElement.previousElementSibling.select()
                el.parentElement.previousElementSibling.setSelectionRange(0, 99999)
            }else{
                el.previousElementSibling.select()
                el.previousElementSibling.setSelectionRange(0, 99999)
            }
            document.execCommand('copy')
            Swal.fire(title,text,'success')
        }
    </script>
@endpush
<?php endif; ?>
