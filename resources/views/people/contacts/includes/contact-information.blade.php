{{--
this page requires:
- Contact $contact
--}}
<div class="card-deck">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                @if (array_get($contact, 'type') === 'organization')
                    @lang('Organization Info')
                @else
                    @lang('Contact Info')
                @endif
            </h5>
            <div class="row">
                <div class="col-6">
                    <img src="{{ $contact->profile_image_src }}" class="img-fluid rounded-lg" />
                </div>
                <div class="col-6">
                    <p class="card-text" style="min-width: 25ch">
                        <p class="mb-0">
                            <strong>
                                {{ array_get($contact, 'full_name') }}
                            </strong>
                        </p>
                        <p class="mb-0">
                            @if (array_get($contact, 'unsubscribed_permanently'))
                                <span class="badge badge-warning cursor-help" data-toggle="tooltip" title="@lang('Permanently unsubscribed from all emails, cannot re-subscribe.')">{{ array_get($contact, 'email_1') }}</span>
                            @elseif (array_get($contact, 'unsubscribed'))
                                <span class="badge badge-warning cursor-pointer" data-tooltip="tooltip" title="@lang('Unsubscribed from all emails, click to re-subscribe')" data-toggle="modal" data-target="#resubscribe-modal">{{ array_get($contact, 'email_1') }}</span>
                            @elseif (array_get($contact, 'unsubscribed_lists_names'))
                                <span class="badge badge-warning cursor-help" data-toggle="tooltip" title="@lang('Unsubscribed from these email lists:') {{ array_get($contact, 'unsubscribed_lists_names') }}">{{ array_get($contact, 'email_1') }}</span>
                            @else
                                {{ array_get($contact, 'email_1') }}
                            @endif
                        </p>
                        <p class="mb-0">
                            @if (!array_get($contact, 'has_us_phone_number'))
                                <span class="badge badge-danger cursor-help" data-toggle="tooltip" title="@lang('Non US phone number')">{{ array_get($contact, 'cell_phone') }}</span>
                            @elseif (array_get($contact, 'unsubscribed_from_phones'))
                                <span class="badge badge-warning cursor-pointer" data-tooltip="tooltip" title="@lang('Unsubscribed')" data-toggle="modal" data-target="#resubscribe-phone-modal">{{ array_get($contact, 'cell_phone') }}</span>
                            @else
                                {{ array_get($contact, 'cell_phone') }}
                            @endif
                        </p>
                        @if(!is_null(array_get($contact, 'website')))
                            <p class="mb-0">
                                {{ array_get($contact, 'website') }}
                            </p>
                        @endif
                        @if(array_get($contact, 'type') === 'person' && !is_null(array_get($contact, 'company')))
                            <p class="mb-0">
                                {{ array_get($contact, 'company') }}
                            </p>
                        @endif
                        @if(!is_null(array_get($contact, 'dob')))
                            <p class="mb-0">
                                {{ date('F jS Y', strtotime(array_get($contact, 'dob'))) }}
                            </p>
                        @endif
                        <p class="mb-0">
                            {{ array_get($contact, 'orderedAddresses.0.mailing_address_1') }}
                        </p>
                        <p class="mb-0">
                            {{ array_get($contact, 'orderedAddresses.0.city') }}
                            @if(!is_null(array_get($contact, 'orderedAddresses.0.region')))
                                , {{ array_get($contact, 'orderedAddresses.0.region') }}.
                            @endif
                            {{ array_get($contact, 'orderedAddresses.0.postal_code') }}
                        </p>
                    </p>
                </div>
            </div>
        </div>
        <div class="card-footer text-center">
            @can('update',$contact)
                <a href="{{ route('contacts.edit', $contact->id)}}" class="btn btn-primary">
                    <i class="fa fa-edit"></i>
                    @if (array_get($contact, 'type') === 'organization')
                        @lang('Edit Organization')
                    @else
                        @lang('Edit Contact')
                    @endif
                </a>
            @endcan
        </div>
    </div>
    @if(auth()->user()->can('contact-background'))
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Background Info</h5>
            <p class="card-text" style="min-width: 30ch">
                <p class="mb-0">
                    {{ $contact->background_info ?:'' }}
                </p>
            </p>
        </div>
        <div class="card-footer text-center">
            @can('update',$contact)
                <button data-toggle="modal" data-target="#edit_background_info" class="btn btn-primary"><i class="fa fa-user-plus"></i> Edit Background Info</button>
            @endcan
        </div>
    </div>
    @endif
    @if(auth()->user()->can('contact-notes'))
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Latest Note</h5>
            <p class="card-text" style="min-width: 30ch">
                @php
                $noteslink = route('contacts.notes', $contact->id);
                $morelink = "<a href='$noteslink'>More</a>";
                $note = $contact->notes()->latest()->first();
                @endphp
                @if (!$note)
                    <em>There are no notes for {{ $contact->first_name }}</em>
                @else
                    @if ($note->title)
                        <strong>{{ $note->title }}</strong><br>
                    @endif
                    {!! str_limit($note->content, 100, "... $morelink") !!}
                @endif
            </p>
        </div>
        <div class="card-footer text-center">
            @can('update',$contact)
                @include('notes.includes.create-button')
            @endcan
        </div>
    </div>
    @endif
</div>

@php
    $relation = $contact;
    $store_note_redirect = route('contacts.show', $contact->id);
@endphp
@include('notes.includes.create-modal')
@can('update',$contact)
    <div class="modal fade" id="edit_background_info">
        <div class="modal-dialog modal-success modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>@lang('Edit Background Info')</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                {{ Form::model($contact, ['route' => ['contacts.update.about', array_get($contact, 'id')], 'method' => 'PUT']) }}
                <div class="modal-body">
                    {{ Form::hidden('uid', Crypt::encrypt(array_get($contact, 'id'))) }}
                    <div class="row">
                        <div class="col-md-12">
                            {{ Form::textarea('background_info', array_get($contact, 'background_info'), ['class' => 'form-control']) }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="btn-submit-contact" type="submit" class="btn btn-primary"><i
                                class="icons icon-note"></i> Save
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endcan

@push('scripts')
<script>
    $('[data-tooltip="tooltip"]').tooltip();
</script>
@endpush
