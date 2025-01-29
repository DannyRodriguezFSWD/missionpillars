@extends('layouts.app')

@section('content')

@include('lists.includes.functions')
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::open(['route' => ['lists.email', $list->id], 'id' => 'form', 'files' => true]) }}
        {{ Form::hidden('uid', Crypt::encrypt($list->id)) }}
        {{ Form::hidden('action', 'preview') }}
        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-0">Send Email to {{ $list->name }}</h4>
            </div>
            <div class="col-md-6 text-right">
                <div class="btn-group btn-group" id="btn-submit-contact">
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
                    {{ Form::label('from', 'From:') }}
                    {{ Form::text('from', auth()->user()->contact->first_name. ' '.auth()->user()->contact->last_name.' ('.auth()->user()->contact->email_1.')', ['class' => 'form-control', 'readonly' => true ]) }}
                </div>
                <div class="form-group">
                    {{ Form::label('to', 'To List:') }}
                    {{ Form::text('to', $list->name, ['class' => 'form-control', 'readonly' => true ]) }}

                    {{ Form::hidden('content') }}
                </div>
                <div class="form-group">
                    {{ Form::label('subject', 'Subject:') }}
                    {{ Form::text('subject', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Subject', 'autocomplete' => 'off' ]) }}
                </div>
                <div id="tinyTextarea" class="tinyTextarea"></div>

            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h5>@lang('Do not send to contacts that have tags for this list')</h5>
                <ol class="tree">
                    <?php printExcludeFoldersTree($tree); ?>
                </ol>
            </div>
        </div>
        {{ Form::close() }}

    </div>

    <div class="card-footer">&nbsp;</div>
</div>
@include('people.contacts.includes.emails.email-empty-modal')
@include('includes.overlay')

@push('scripts')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
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
                    subject: $("input[name='subject']").val(),
                    content: markupStr,
                    action: action
                };
                
                $.post("{{ route('lists.email', ['id' => $list->id]) }}", data, function(result){
                    console.log(result);
                    if (result === true) {
                        Swal.fire("@lang('Test email was sent')",'','success');
                    } else {
                        Swal.fire('',result,'error');
                    }
                    $('#overlay').fadeOut();
                }).fail(function (result) {
                    console.log(result);
                    Swal.fire("@lang('Oops! Something went wrong. [404]')",'','error');
                });
            }
        });

        $('button[type="submit"]').on('click', function () {
            $("input[name='action']").val($(this).prop('name'));
        });


        var top = 84;
        $(window).scroll(function () {
            var y = $(this).scrollTop();
            var button = $('#btn-submit-contact');
            if (y >= top) {
                button.css({
                    'position': 'fixed',
                    'top': '60px',
                    'right': '36px',
                    'z-index': '99'
                });
            } else {
                button.removeAttr('style')
            }
        });
    })();
</script>
@endpush

@endsection
