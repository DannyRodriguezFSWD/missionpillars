@php
    $sent = \App\Models\SMSSent::findOrFail(array_get($post, 'timeline_amount'));
@endphp
<li class="timeline-inverted">
    <div class="timeline-badge {{$status}}"><i class="fa fa-commenting-o"></i></div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">
                @if(array_get($post, 'timeline_status') == 'received')
                    @if(is_null(array_get($sent, 'createdBy')))
                    <a target="_blank" href="{{ route('contacts.show', ['id' => array_get($sent, 'from.id')]) }}">
                        {{ ucwords(array_get($sent, 'from.first_name')) }} {{ ucwords(array_get($sent, 'from.last_name')) }}
                    </a>
                    @else
                    <a target="_blank" href="{{ route('contacts.show', ['id' => array_get($contact, 'id')]) }}">
                        {{ ucwords(array_get($contact, 'first_name')) }} {{ ucwords(array_get($contact, 'last_name')) }}
                    </a>
                    @endif
                    replied a SMS message
                @elseif(array_get($post, 'timeline_status') == 'Queued')
                    A SMS <span class="text-info">
                        is queued to send
                    </span>
                @elseif(array_get($post, 'timeline_status') == 'error')
                    A SMS <span class="text-danger">
                        failed to send
                    </span>
                @else
                    A SMS was sent
                @endif
            </h4>
            <p class="mb-0">
                <small>
                    <i class="fa fa-clock-o"></i>
                    {{ displayLocalDateTime(array_get($post, 'timeline_date'))->toDayDateTimeString() }}
                </small>
            </p>
            <p>
                <small>
                    <i class="fa fa-mobile"></i> To:
                    {{ array_get($post, 'timeline_campaign') }}
                </small>
            </p>
            @if(!is_null(array_get($sent, 'createdBy')))
                <p>Sent by {{ array_get($sent, 'createdBy.contact.first_name') }} {{ array_get($sent, 'createdBy.contact.last_name') }}</p>
            @endif
            <p>
                <i>"{{ array_get($post, 'timeline_subject') }}"</i>
            </p>

            @if (array_get($post, 'timeline_status') !== 'error' && !$phoneNumbers->contains(array_get($post, 'timeline_campaign')))
            <p>
                <div class="alert alert-info">
                    You are unable to reply to this message because you do not have access to the phone number it was sent to.
                </div>
            </p>
            @elseif(array_get($post, 'timeline_status') == 'received')
            <p>
                <button class="btn btn-primary" data-target="#reply-modal-{{ array_get($sent, 'id') }}" data-toggle="modal">
                    Click here to reply
                    <i class=" fa fa-reply"></i>
                </button>
            </p>
            @endif

        </div>
        <div class="timeline-body">

            <div class="modal fade" id="reply-modal-{{ array_get($sent, 'id') }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-primary modal-lg" role="document">
                    <div class="modal-content">
                    {{ Form::open(['route' => ['twilio.sms.send.reply', base64_encode(array_get($sent, 'id'))]]) }}
                        <div class="modal-header">
                            <h4 class="modal-title">@lang('Reply sms')</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="card animated fadeIn m-4">
                                <div class="card-header">
                                    @lang(' In reply to')
                                    @if(is_null(array_get($sent, 'createdBy')))
                                        {{ ucwords(array_get($sent, 'from.first_name')) }} {{ ucwords(array_get($sent, 'from.last_name')) }}
                                    @else
                                        {{ ucwords(array_get($contact, 'first_name')) }} {{ ucwords(array_get($contact, 'last_name')) }}
                                    @endif
                                </div>
                                <div class="card-body">

                                    <main class="message">
                                        <div class="details">
                                            <div class="header">
                                                    <div class="from">
                                                        <strong>{{ array_get($sent, 'createdBy.tenant.organization') }}</strong>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="content">
                                                    <p>{{ array_get($sent, 'content.content') }}</p>
                                                </div>
                                                
                                                <hr>
                                                
                                                <div class="form-group">
                                                    {{ Form::textarea('content', null, ['class' => "form-control", 'placeholder' => "Enter text here to reply", 'rows' => "6", 'required' => true]) }}
                                                </div>
                                            </div>
                                        </main>
                                    </div>

                                    <div class="card-footer">&nbsp;</div>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('Reply')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                        </div>
                    {{ Form::close() }}
                    </div>

                </div>
            </div>

        </div>

    </div>
</li>
