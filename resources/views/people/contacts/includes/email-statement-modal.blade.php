<div class="modal fade" id="email-statement-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'print-mail.store', 'name' => 'email-form']) }}
            {{ Form::hidden('contact_id', array_get($contact, 'id')) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Email Statement')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="col-form-label">@lang('Subject') *</label>
                                <input type="text" placeholder="Subject" name="name" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="reply_to" class="col-form-label">From Name:</label>
                                <input type="text" name="from_name" value="{{auth()->user()->name .' ' . auth()->user()->last_name}}" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="reply_to" class="col-form-label">Reply To</label>
                                <input type="text" name="reply_to" value="{{auth()->user()->email}}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-12 d-flex">
                                        <label class="c-switch c-switch-label c-switch-primary mr-2">
                                            <input type="checkbox" class="c-switch-input" name="cc_secondary" @if(old('cc_secondary')) checked @endif>
                                            <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                                        </label>
                                        <label for="cc_secondary">Cc secondary email</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4 mb-2">
                        <div class="col-md-12">
                            {{-- <h5>{{ Form::checkbox('use_date_range', 1, null, ['style' => 'margin-top: -5px;']) }} Limit print mail for contacts with transaction between the date range below</h5> --}}
                            <h4>{{ Form::hidden('use_date_range', 1) }} Transaction date range:</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            @include('_partials.human_date_range_selection',[
                                'fromDateName' => 'start_date',
                                'toDateName' => 'end_date',
                                'defaultSelectedRange' => 'Last Month',
                                'prefix' => 'email_statement'
                            ])
                        </div>
                    </div>

                </div>

                <div class="card-body">
                    <label for="content">@lang('Email content') *</label>
                    {{ Form::textarea('statement', array_get($statement, 'content', array_get($templates, '0.print_content')), ['id' => 'email_statement_tiny', 'data-buttons' => 'statement']) }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="emailBtn">@lang('Send')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
            {{ Form::close() }}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@push('scripts')
    <script type="text/javascript">
        (function () {
            $('#emailBtn').click(function () {
                let data = $('form[name="email-form"]').serializeArray();
                let params = {}
                data.forEach(item => {
                    params[item.name] = item.value
                })
                params.statement = tinymce.get('email_statement_tiny').getContent();
                params.isEmail = true;
                if (!params.name || !params.statement || !params.start_date || !params.end_date){
                    Swal.fire('Please fill out the required details','Subject and content fields are Required!','info')
                    return false
                }
                $('#overlay').show()
                axios.post('/crm/print-mail', params)
                    .then(res => {
                        Swal.fire('Success!','Email Sent Successfully!','success')
                        $('#email-statement-modal').modal('hide');
                        $('form[name="email-form"]').trigger("reset");
                    })
                    .catch(err => {
                        Swal.fire('Please fill out the required details','Subject and content fields are Required!','info')
                    })
                    .finally(function (){
                        $('#overlay').hide()
                    })
            })
        })();

        initTinyEditor({
            selector: '#email_statement_tiny',
            height: 320,
            toolbar: 'undo redo | formatselect | bold italic | bullist numlist outdent indent | mailmerge | customtemplates',
            plugins: 'preview powerpaste casechange importcss tinydrive searchreplace autolink autosave save directionality advcode visualblocks visualchars fullscreen image link media mediaembed template codesample table charmap pagebreak nonbreaking anchor tableofcontents insertdatetime advlist lists checklist wordcount editimage help formatpainter permanentpen pageembed charmap emoticons advtable',
            setup: function (editor) {
                editor.ui.registry.addMenuButton('mailmerge', {
                    text: 'Mail Merge',
                    fetch: function (callback) {
                        let items = [];

                        mergeTags.forEach(function (item, index) {
                            items.push({
                                type: 'menuitem',
                                text: item.name,
                                onAction: function () {
                                    editor.insertContent(item.code);
                                }
                            });
                        });

                        callback(items);
                    }
                });

                editor.ui.registry.addMenuButton('customtemplates', {
                    text: 'Templates',
                    fetch: function (callback) {
                        let items = [];

                        templatesInTiny.forEach(function (item, index) {
                            items.push({
                                type: 'menuitem',
                                text: item.name,
                                onAction: function () {
                                    editor.setContent(contentTempaltes[item.code].content);
                                }
                            });
                        });

                        callback(items);
                    }
                });

                editor.on('init', function(e) {
                    const templates = {!! $templates->toJson() !!}
                    const standardTemplate = templates.find(template => template.name == 'Contribution Stmt. Window Envelope')
                    editor.setContent(standardTemplate.content)
                });
            }
        });
    </script>
@endpush