{{ Form::hidden('download', 'false') }}
<div class="card-body">
    <div class="row">
        <div class="col-sm-2">
            <div class="form-group">
                <label style="padding-top: 10px;">@lang('Name')</label>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group {{ $errors->has('name') ? 'has-danger':'' }}">
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => true]) }}
                @if( $errors->has('name') )
                <span class="text-danger">{{ $errors->first('name') }}</span>
                @endif
            </div>
        </div>
        <div class="col-sm-2 text-right">
            <div class="form-group">
                <label style="padding-top: 10px;">@lang('Print for')</label>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::select('print_for', $print_for, null, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
    <div class="row mt-4 mb-2">
        <div class="col-md-12">
            {{-- <h5>{{ Form::checkbox('use_date_range', 1, null, ['style' => 'margin-top: -5px;']) }} Limit print mail for contacts with transaction between the date range below</h5> --}}
            <h5>{{ Form::hidden('use_date_range', 1) }} Limit print mail for contacts with transaction between the date range below</h5>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @include('_partials.human_date_range_selection',[
                'fromDateName' => 'start_date',
                'toDateName' => 'end_date',
                'defaultSelectedRange' => 'Last Month',
                'prefix' => 'pstate',
            ])
        </div>
    </div>
    
</div>

<div class="card-body">
    {{ Form::textarea('statement', array_get($statement, 'content', array_get($templates, '0.print_content')), ['class' => 'tinyTextarea', 'data-buttons' => 'statement']) }}
</div>

@push('scripts')
<script type="text/javascript">
    (function () {
        $('#pdf').on('click', function (e) {
            $('input[name="download"]').val('true');
            $('#print').click();
        });

    })();

    initTinyEditor({
        selector: '.tinyTextarea',
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
