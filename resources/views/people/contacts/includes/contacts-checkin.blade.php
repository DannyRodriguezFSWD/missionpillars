@foreach ($contacts as $contact)
<div class="card mb-1 cursor-pointer" onclick="selectContact(this);" data-contactId="{{ array_get($contact, 'id') }}">
    <div class="card-body p-3">
        <div class="d-table">
            @if ($contact->isChecked && !$contact->checked_out_time)
            <span class="d-table-cell pr-3 align-middle">
                <i class="fa fa-check-square fa-3x text-success"></i>
            </span>
            @elseif ($contact->isChecked && $contact->checked_out_time)
            <span class="d-table-cell pr-3 align-middle">
                <i class="fa fa-close fa-3x text-muted"></i>
            </span>
            @else
            <span class="d-table-cell pr-3 align-middle">
                <i class="fa fa-square-o fa-3x text-info"></i>
            </span>
            @endif
            <span class="d-table-cell">
                <img src="{{ $contact->profile_image_src }}" class="img-fluid rounded-circle" style="width: 70px;" alt="{{ $contact->full_name }}" />
            </span>
            <span class="d-table-cell align-middle pl-2">
                {{ $contact->last_name }} {{ $contact->first_name }}
                @if ($contact->email_1)
                ({{ $contact->email_1 }})
                @endif
                @if ($contact->child_checkin_note)
                <br>
                <small class="text-muted">{{ $contact->child_checkin_note }}</small>
                @endif
                <br>
                @if ($contact->checked_in_time)
                <small class="text-muted checkinTime">
                    {{ displayLocalDateTime($contact->checked_in_time)->format('H:i') }}
                    @if ($contact->checked_out_time)
                    - {{ displayLocalDateTime($contact->checked_out_time)->format('H:i') }}
                    @endif
                </small>
                @endif
            </span>
            
        </div>
        @if ($contact->printed_tag)
        <button class="btn btn-primary pull-right nocheck" data-tooltip="tooltip" title="Mark for reprint" style="margin-top: -50px;" onclick="reprintTag(this, {{ array_get($contact, 'id') }})">
            <i class="fa fa-print"></i>
        </button>
        @endif
    </div>
</div>
@endforeach
