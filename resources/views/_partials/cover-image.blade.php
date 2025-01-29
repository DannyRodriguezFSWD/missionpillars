<div class="card shadow-lg">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <h5>
                    @isset($title)
                    {{ $title }}
                    @else
                    Cover Image
                    @endisset
                </h5>
            </div>
            <div class="col-12">
                <div class="m-4 text-center">
                    <label for="image">
                        <img id="renderImage" onmouseenter="$(this).css('opacity','.8')" onmouseout="$(this).css('opacity','1')"
                             src="{{ $imagePath ? asset($imagePath) : asset('img/blank_placeholder.png') }}"
                             class="img-responsive p-1" style="max-height: 35vh; border: 1px dashed black; cursor:pointer;"/>
                    </label>
                    <h4 class="text-center">Drop an Image or Click to Upload.</h4>
                    <button type="button" class="btn btn-sm btn-secondary d-none" id="unsetImage"><i class="fa fa-undo"></i></button>
                    @if($showRemoveButton)
                        <button type="button" class="btn btn-sm btn-danger" id="removeImage"><i class="fa fa-trash"></i> Remove Cover Image</button>
                    @endif
                </div>

                <div class="form-group">
                    <input class="d-none" accept="image/png, image/gif, image/jpeg" data-render-to=".eventImageUpload" type="file" id="image">
                    <input class="d-none" accept="image/png, image/gif, image/jpeg" name="image" data-render-to=".eventImageUpload" type="file" id="image2">
                    <input type="hidden" name="removeCoverImage">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cropperModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crop Image to 16 / 9 Ratio</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-10 offset-md-1">
                        <img src="" class="eventImageUpload img-fluid" id="cropperRenderImage" alt="">
                    </div>
                    <div class="col-12 text-center mt-2">
                        <input type="range" step="0.1" min="0" max="4" id="zoomRange" class="form-control-range">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="saveCropImage" type="button" class="btn btn-success">Ok</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.5/cropper.min.css" integrity="sha512-Aix44jXZerxlqPbbSLJ03lEsUch9H/CmnNfWxShD6vJBbboR+rPdDXmKN+/QjISWT80D4wMjtM4Kx7+xkLVywQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.5/cropper.min.js" integrity="sha512-E4KfIuQAc9ZX6zW1IUJROqxrBqJXPuEcDKP6XesMdu2OV4LW7pj8+gkkyx2y646xEV7yxocPbaTtk2LQIJewXw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    let fileImage = null;
    (function () {
        let imageInput = document.getElementById('image')
        let dropContainer = document.getElementById('renderImage')
        dropContainer.ondragover = dropContainer.ondragenter = function(evt) {
            evt.preventDefault();
            dropContainer.classList.add('drop-active')
        };

        ['dragleave','dragend'].forEach(ev => {
            dropContainer.addEventListener(ev,function (evt) {
                dropContainer.classList.remove('drop-active')
            })
        })

        dropContainer.ondrop = function(evt) {
            dropContainer.classList.remove('drop-active')
            if (document.getElementById('image').accept.split(', ').indexOf(evt.dataTransfer.files[0].type) == -1){
                Swal.fire('Invalid Image','Please drop a valid image','info')
                return false
            }
            imageInput.files = evt.dataTransfer.files;
            $(imageInput).trigger('input')

            evt.preventDefault();
        };

        let $modal = $('#cropperModal');
        let image = document.getElementById('cropperRenderImage');
        let cropper;
        let file;
        $("#image").on("input change", function(e){
            let files = e.target.files;
            let done = function(url) {
                image.src = url;
                $modal.modal('show');
            };
            let reader;
            if (files && files.length > 0) {
                file = files[0];
                if (isValidFileImage(file) == false) {
                    Swal.fire('Invalid Image', 'Please select a valid image', 'info')
                    return false
                }
                if (URL) {
                    done(URL.createObjectURL(file));
                } else if (FileReader) {
                    reader = new FileReader();
                    reader.onload = function(e) {
                        done(reader.result);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
        $modal.on('shown.coreui.modal', function() {
            cropper = new Cropper(image, {
                aspectRatio: @isset($aspectRatio) {{ $aspectRatio }} @else 16 / 9 @endisset,
                viewMode: 1,
            });
            document.getElementById('zoomRange').value = 1;
            document.getElementById('zoomRange').addEventListener('input',function (e) {
                cropper.zoomTo(e.target.value)
            })
        }).on('hide.coreui.modal', function(e) {
            if (document.activeElement.id != 'saveCropImage') document.getElementById('image').value = '';
            cropper.destroy();
            cropper = null;
        });
        $("#saveCropImage").on("click", function() {
            canvas = cropper.getCroppedCanvas();
            canvas.toBlob(function(blob) {
                fileImage = new File([blob], file.name,{type:file.type, lastModified:new Date().getTime()});
                document.querySelector('[name="removeCoverImage"]').value = ''
                let reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function() {
                    let base64data = reader.result;
                    document.getElementById('renderImage').setAttribute('src',base64data) ;
                    $modal.modal('hide');
                    $('#unsetImage').removeClass('d-none')
                }
            },file.type);
        })
    })()
</script>
<script>
    (function () {
        let oldImage = "{{ $imagePath ? asset($imagePath) : asset('img/blank_placeholder.png') }}"
        document.getElementById('unsetImage').addEventListener('click',function () {
            fileImage = null;
            document.getElementById('renderImage').setAttribute('src',oldImage)
            document.querySelector('[name="removeCoverImage"]').value = ''
            $('#unsetImage').addClass('d-none')
            if ($('#removeImage')) $('#removeImage').removeClass('d-none');
        })
        @if($showRemoveButton)
        let defaultBlankImage = "{{asset('img/blank_placeholder.png') }}";
        document.getElementById('removeImage').addEventListener('click',function () {
            fileImage = null;
            document.querySelector('[name="removeCoverImage"]').value = '1'
            document.getElementById('renderImage').setAttribute('src',defaultBlankImage)
            $('#unsetImage').removeClass('d-none')
            $('#removeImage').addClass('d-none')
        })
        @endif
    })()
</script>
@endpush