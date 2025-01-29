@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('people.contacts.includes.card-header')
            </div>
            <div class="card-body">
                {{ Form::open(['route' => ['contacts.email', $contact->id], 'id' => 'form', 'files' => true]) }}
                {{ Form::hidden('uid', Crypt::encrypt($contact->id)) }}
                {{ Form::hidden('action', 'preview') }}
                <div class="row">
                    <div class="col-md-6">
                        &nbsp;
                    </div>
                    <div class="col-md-6 text-right pb-2">
                        <div class="" id="floating-buttons">
                            <button type="submit" class="btn btn-primary" name="preview">
                                <i class="icons icon-envelope-letter"></i> @lang('Send Test Email')
                            </button>
                            <button type="submit" class="btn btn-success" name="send">
                                <i class="icons icon-paper-plane"></i> @lang('Send')
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('to', 'To:') }}
                            {{ Form::text('to', array_get($contact, 'first_name'). ' '.array_get($contact, 'last_name').' ('.array_get($contact, 'email_1').')', ['class' => 'form-control', 'readonly' => true ]) }}
                            {{ Form::hidden('content') }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('from_name', 'From Name:') }}
                            {{ Form::text('from_name', array_get(auth()->user(), 'name') . ' ' . array_get(auth()->user(), 'last_name'), ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('reply_to', 'Reply To:') }}
                            {{ Form::text('reply_to', array_get(auth()->user(), 'email'), ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('subject', 'Subject:') }}
                            {{ Form::text('subject', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Subject', 'autocomplete' => 'off' ]) }}
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-12 col-md-3 d-flex">
                                    <label class="c-switch c-switch-label c-switch-primary mr-2">
                                        <input type="checkbox" class="c-switch-input" name="cc_secondary" @if(old('cc_secondary')) checked @endif>
                                        <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                                    </label>
                                    <label for="cc_secondary">Cc secondary email</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex">
                            <label class="c-switch c-switch-label  c-switch-primary">
                                <input type="checkbox" name="include_transactions" id="include_transactions_input" @change="togglePending" class="c-switch-input" checked>
                                <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                            </label>
                            <div class="ml-2">Include Transactions</div>
                        </div>
                        <div class="form-group" id="include_transactions_date_div">
                            @include('_partials.human_date_range_selection',[
                                'fromDateName' => 'start_date',
                                'fromDateVal' => old('start_date'),
                                'toDateName' => 'end_date',
                                'toDateVal' => old('end_date'),
                                'defaultSelectedRange' => 'Last Month',
                                'prefix' => 'contacts_composer'
                            ])
                        </div>
                        
                        <div class="form-group">
                            <label>@lang('Select Template')</label>
                            <select class="form-control" id="selectTemplate">
                                <option value="">-- select a template --</option>
                            </select>
                        </div>
                        
                        <div id="tinyTextarea" class="tinyTextarea"></div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>

        </div>

    </div>
    <!--/.col-->

</div>
<!--/.row-->

@include('people.contacts.includes.emails.email-empty-modal')
@include('people.contacts.includes.emails.email-mime-modal')
@include('includes.overlay')

@push('scripts')
<script type="text/javascript">
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $('#include_transactions_input').change(function () {
      if ($(this).is(':checked')){
          $('#include_transactions_date_div').show()
      }else {
          $('#include_transactions_date_div').hide()
      }
  })
    const contentTempaltes = {!! $templates->keyBy('id')->toJson() !!};
    initTinyEditor({
        selector: '#tinyTextarea',
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
        }
    });

    (function () {
        $('#form').on('submit', function (e) {
            var markupStr = tinymce.get("tinyTextarea").getContent();

            if (markupStr === '') {
                Swal.fire('Email Composer', 'Email content cannot be empty','info');
                return false;
            }

            $("input[name='content']").val(markupStr);

            var action = $("input[name='action']").val();
            if (action === 'preview') {
                e.preventDefault();
                $('#overlay').fadeIn();
                var data = {
                    from_name: $("input[name='from_name']").val(),
                    subject: $("input[name='subject']").val(),
                    reply_to: $("input[name='reply_to']").val(),
                    cc_secondary: $( "input[name='cc_secondary']" ).prop('checked'),
                    content: markupStr,
                    action: action
                };

                $.post("{{ route('contacts.email', ['id' => $contact->id]) }}", data, function (result) {
                    if (result === true) {
                        Swal.fire("@lang('Test email was sent')",'','success');
                    } else {
                        Swal.fire('',result,'info');
                    }
                    $('#overlay').fadeOut();
                }).fail(function (result) {
                    console.log(result.responseText);
                    $('#overlay').fadeOut();
                    Swal.fire("@lang('Oops! Something went wrong. [404]')",'','error');
                });
            }
        });

        $('button[type="submit"]').on('click', function () {
            $("input[name='action']").val($(this).prop('name'));
        });

        templatesInTiny.forEach(function (item, index) {
            $('#selectTemplate').append('<option value="'+item.code+'">'+item.name+'</option>');
        });
        
        $('#selectTemplate').change(function () {
            let templates = {!! $templates->toJson() !!}
            let selectedTemplate = templates.find(template => template.id == $(this).val())
            tinymce.get('tinyTextarea').setContent(selectedTemplate.content);
        });
    })();
</script>
@endpush
@endsection
