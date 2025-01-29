<div class="row">
    <div class="col-md-6">
        <h3>@lang('Step 1')</h3>
    </div>
    <div class="col-md-6 text-right pb-2">
        <div class="" id="floating-buttons">
            <button type="submit" class="btn btn-secondary" name="preview">
                <i class="icons icon-envelope-letter"></i> @lang('Send Test Email')
            </button>
            <button type="submit" class="btn btn-primary" name="send">
                @lang('Next')
                <i class="icon icon-arrow-right"></i>
            </button>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {{ Form::label('from_name', 'From Name:') }}
            {{ Form::text('from_name', array_get($email, 'from_name', array_get(auth()->user(), 'contact.first_name').' '.array_get(auth()->user(), 'contact.last_name')), ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
        </div>
        <div class="form-group">
            {{ Form::label('from_email', 'From Email:') }}
            {{ Form::email('from_email', array_get($email, 'from_email', array_get(auth()->user(), 'contact.email_1')), ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
        </div>
        <div class="form-group">
            {{ Form::label('list_id', 'To List:') }}
            {{ Form::select('list_id', $lists, null, ['class' => 'form-control', 'readonly' => true ]) }}

            {{ Form::hidden('content') }}
        </div>
        <div class="form-group">
            {{ Form::label('subject', 'Subject:') }}
            {{ Form::text('subject', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Subject', 'autocomplete' => 'off' ]) }}
        </div>
        <div id="tinyTextarea" class="tinyTextarea">
            {!! array_get($email, 'content') !!}
        </div>

    </div>
</div>

@push('scripts')
<script type="text/javascript">
    initTinyEditor();
    
    (function () {
        $('#form').on('submit', function (e) {
            var markupStr = tinymce.get("tinyTextarea").getContent();
            
            if (markupStr === '') {
                $('#email-empty-modal').modal();
                return false;
            }
            
            $("input[name='content']").val(markupStr);

            var action = $("input[name='action']").val();
            if (action === 'preview') {
                e.preventDefault();
                $('#overlay').fadeIn();
                var data = {
                    from_name: $("input[name='from_name']").val(),
                    from_email: $("input[name='from_email']").val(),
                    subject: $("input[name='subject']").val(),
                    content: markupStr,
                    action: action
                };

                $.post("{{ route('emails.preview', ['id' => array_get($email, 'id', 0)]) }}", data).done(function (result) {
                    if (result === true) {
                        Swal.fire("@lang('Test email was sent')",'','success');
                    } else {
                        Swal.fire('',result,'info');
                    }
                    $('#overlay').fadeOut();
                }).fail(function (result) {
                    console.log(result.responseText);
                    Swal.fire("@lang('Oops! Something went wrong. [404]')",'','error');
                });
            }
        });

        $('button[type="submit"]').on('click', function () {
            $("input[name='action']").val($(this).prop('name'));
        });
        
    })();
</script>
@endpush