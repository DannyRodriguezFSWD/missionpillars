<div class="card-body mb-0 pb-0">
    @if (!is_null($contact))
        <p>
            {{ Form::open(['route' => ['entries.update', array_get($entry, 'id')], 'method' => 'PUT', 'id' => 'unlink-'.array_get($contact, 'id')]) }}
            {{ Form::hidden('uid', Crypt::encrypt(array_get($entry, 'id'))) }}
            {{ Form::hidden('contact', array_get($contact, 'first_name').' '.array_get($contact, 'last_name').' ('.array_get($contact, 'email_1').')') }}
            {{ Form::hidden('cid',Crypt::encrypt( array_get($contact, 'id'))) }}
            {{ Form::hidden('link_action', 'unlink') }}
            {{ Form::hidden('relationship', 'FormContact') }}
            <button class="btn btn-danger btn-sm detach-form-entry" type="button" data-action="unlink" data-id="{{ array_get($contact, 'id') }}">
                <i class="fa fa-close"></i>
                Unlink
            </button>
            <button type="button" class="btn btn-link btn-sm help-button" data-toggle="modal" data-target="#help-modal" data-content="This action will unlink this form from {{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }} <strong>({{ array_get($contact, 'email_1') }})</strong>.<br>Form entry won't be deleted.">
                <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;"></i>
            </button>
            This form is linked to
            <a target="_blank" href="{{ route('contacts.show', ['id' => array_get($contact, 'id')]) }}">
                {{ array_get($contact, 'first_name') }}
                {{ array_get($contact, 'last_name') }}
                ({{ array_get($contact, 'email_1') }})
            </a>
            {{ Form::close() }}
        </p>
    @elseif (is_null($contact))
        <p>This form is not linked to a contact</p>
        @if(count($match) > 0)
            <p class="pb-0 mb-0">@lang('We found these matches')</p>
            @include('entries.includes.contact_list', ['relationship' => array_get(\App\Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT')])
        @else
            <p>@lang("No matches found")</p>
        @endif
        <p>
            @if (count($match) > 0)
                @lang('Or you ')
            @else
                @lang('You ')
            @endif
            @lang('can link this form entry manually using following options')
        </p>
        <div class="btn-group btn-group" role="group" aria-label="...">
            {{ Form::open(['route' => 'entries.store', 'id' => 'link-new-contact']) }}
            {{ Form::hidden('id', array_get($entry, 'id')) }}
            {{ Form::hidden('relationship', array_get(\App\Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT')) }}
            <button type="button" class="btn btn-primary attach-form-entry" data-action="link" data-id="new-contact">
                <i class="fa fa-user-plus"></i>
                @lang('Link as new contact')
            </button>
            {{ Form::close() }}
            <button id="existing-contact" type="button" class="btn btn-primary" data-toggle="modal" data-target="#select-contact-modal">
                <i class="fa fa-user"></i>
                @lang('Link to existing contact')
            </button>
        </div>
    @endif

    @if (array_get($form, 'accept_payments'))
        <div class="m-4">&nbsp;</div>
        @if(array_get($entry, 'transaction.status') == 'complete')
            <div class="text-success mb-0">
                A payment of <strong>${{ number_format(array_get($entry, 'transaction.splits.0.amount', 0), 2) }}</strong> was received on this form
            </div>
        @elseif(array_get($entry, 'transaction.status') == 'pending')
            <div class="text-danger">
                There is a pending payment for
                <strong>${{ number_format(array_get($fields, 'total', 0), 2) }}</strong> on this form
            </div>
        @else
            <p class="text-danger pb-0 mb-0">
                This form requires payment of
                <strong>${{ number_format(array_get($fields, 'total', 0), 2) }}</strong>
            </p>
            <p>
                Here is a link to the payment page so this form submission can be paid for:<br>
                @php $url = sprintf(env('APP_DOMAIN'), array_get(auth()->user(), 'tenant.subdomain')).'forms/'.array_get($form, 'uuid').'/payment?contact_id='.array_get($contact, 'id').'&entry_id='.array_get($entry, 'id').'&total='.array_get($fields, 'total', 0) @endphp
                <a target="_blank" href="{{ $url }}">{{ $url }}</a>.
            </p>
        @endif


        @if (!is_null($payer))
            <p>
                Form paid by
                <a target="_blank" href="{{ route('contacts.show', ['id' => array_get($payer, 'id')]) }}">
                    {{ array_get($payer, 'first_name') }}
                    {{ array_get($payer, 'last_name') }}
                    ({{ array_get($payer, 'email_1') }})
                </a>
            </p>
            @if(!is_null($split))
            <a href="{{ route('transactions.show', ['id' => array_get($split, 'id')]) }}" class="btn btn-primary" target="_blank">
                <i class="fa fa-external-link" aria-hidden="true"></i>
                Show payment
            </a>
            @endif
        @endif

    @endif
</div>


<div class="card-body mt-4">
    <div class="forms-info">
        <h2>
            {{ array_get($form, 'name') }}
        </h2>
        <div class="mt-4"></div>
        <div id="fb-rendered-form"></div>
    </div>
</div>

<div class="modal fade" id="help-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Help')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p></p>
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
<script src="{{ asset('js/forms/form-builder.min.js')}}"></script>
<script src="{{ asset('js/forms/form-render.min.js')}}"></script>
<script>
    (function(){
        $('.help-button').on('click', function(e){
            $('#help-modal .modal-body p').html($(this).data('content'));
        });
    })();
</script>
<script type="text/javascript">
    (function(){
        var container = document.getElementById('fb-rendered-form');
        var formData = {!! array_get($form, 'json', '[]') !!}
        var formRenderOpts = {
            container,
            formData,
            dataType: 'json'
        };
        $(container).formRender(formRenderOpts);

        var data = {!! $json !!};
        formData.forEach(function(item){
            if(item.type === 'text'){
                let component = $('input[name="'+item.name+'"][type="'+item.subtype+'"]');
                component.val(data[item.name]);
            }
            else if(item.type === 'number'){
                let component = $('input[name="'+item.name+'"][type="number"]');
                component.val(data[item.name]);
            }
            else if(item.type === 'select'){
                let component = $('select[name="'+item.name+'"]');
                component.val(data[item.name]);
            }
            else if(item.type === 'textarea'){
                let component = $('textarea[name="'+item.name+'"]');
                component.html(data[item.name]);
            }else if (item.type === 'radio-group'){
                if (!item.name.includes('[]')) {
                    let name = item.name.replace('[]','');
                    let element = document.querySelector('input[name="'+item.name+'"][type="radio"][value="'+data[name]+'"]');
                    if (element) element.checked = true;
                }
            } else if (item.type === 'file') {
                let component = $('input[name="'+item.name+'"][type="file"]');
                let componentParent = component.parent();
                component.remove();
                if (data[item.name]) {
                    componentParent.append('<a href="/crm/documents/'+data[item.name]+'/download" target="_blank" class="btn btn-primary btn-sm ml-3">Download File</a>');
                }
            }
        });

        Object.keys(data).forEach(key => {
            if (typeof data[key] == 'object' && data[key]) {
                Object.values(data[key]).forEach((datum, datum_index) => {
                    let components = Array.from(document.querySelectorAll(`[name='${key}[]']`))
                    for (const component of components) {
                        if ((component.type == 'radio' || component.type == 'checkbox') && component.value == datum) {
                            component.checked = true;
                            break;
                        } else if ((component.type == 'text' || component.type == 'number' || component.tagName == 'SELECT') && component.value == '') {
                            component.value = datum;
                            break;
                        }
                    }
                });
            }
        });

        $('.attach-form-entry').on('click', function (e) {
            let selector = '#' + $(this).data('action') + '-' + $(this).data('id');
            Swal.fire({
                title: 'Link to a new contact.',
                text: 'Are you sure you want to link form entry to a new contact',
                type: 'question',
                showCancelButton: true,
            }).then(res => {
                if (res.value) $(selector).submit();
            })
        });

        $('.detach-form-entry').on('click', function(e){
            let selector = '#'+$(this).data('action')+'-'+$(this).data('id');
            Swal.fire({
                title: 'Unlink Contact',
                text: 'Are you sure you want to unlink form entry from this contact',
                type: 'question',
                showCancelButton: true,
            }).then(res => {
                if (res.value) $(selector).submit();
            })
        });

    })();
</script>
@endpush