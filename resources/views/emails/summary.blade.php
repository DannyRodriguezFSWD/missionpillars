@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::open(['route' => ['emails.postconfirm', array_get($email, 'id'), 'list='.array_get($list, 'id')]]) }}
        <div class="row">
            <div class="col-sm-6">
                <h3>@lang('Final step')</h3>
            </div>
            <div class="col-md-6 text-right pb-2">
                <div class="" id="floating-buttons">
                    <a href="{{ route('emails.exclude', ['id' => array_get($email, 'id'), 'list' => array_get($list, 'id')]) }}" class="btn btn-secondary">
                        <i class="icons icon-arrow-left"></i>
                        @lang('Previous')
                    </a>
                    <button type="submit" class="btn btn-primary">
                        @lang('Next')
                        <i class="icons icon-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <p>&nbsp;</p>
        <div class="row">
            <div class="col-sm-6">
                <h5>@lang('Confirm your settings')</h5>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    <div class="card-body">
        <hr/>
        <h2>@lang('Step 1') <small>@lang('Send email to list') <span class="badge-pill badge-primary text-white">{{ array_get($list, 'name', 'Everyone') }}</span></small></h2>
        <h6><span class="fa fa-tags"></span> @lang("List's Tags")</h6>
        <div class="row">
            <div class="col-sm-6">
                <p>@lang('Included')</p>
                <select multiple class="form-control">
                    @foreach(array_get($list, 'inTags', []) as $tag)
                    @if($loop->first)
                    <option value="{{ array_get($tag, 'id') }}" selected="">{{ array_get($tag, 'name') }}</option>
                    @else
                    <option value="{{ array_get($tag, 'id') }}">{{ array_get($tag, 'name') }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6">
                <p>@lang('Excluded')</p>
                <select multiple class="form-control">
                    @foreach(array_get($list, 'notInTags', []) as $tag)
                    @if($loop->first)
                    <option value="{{ array_get($tag, 'id') }}" selected="">{{ array_get($tag, 'name') }}</option>
                    @else
                    <option value="{{ array_get($tag, 'id') }}">{{ array_get($tag, 'name') }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card-body">
        <hr/>
        <h2>@lang('Step 2') <small>@lang('Email subject'): <span class="badge-pill badge-primary text-white">{{ array_get($email, 'subject') }}</span></small></h2>
        <h6>@lang('From name'): <small><span class="badge-pill badge-success text-white">{{ array_get($email, 'from_name') }}</span></small></h6>
        <h6>@lang('From email'): <small><span class="badge-pill badge-success text-white">{{ array_get($email, 'from_email') }}</span></small></h6>
        <p>@lang('Preview')</p>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-control">
                    {!! array_get($email, 'content') !!}
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <hr/>
        <h2> @lang('Step 3')</h2>
        @if( array_get($email, 'send_to_all') )
        <h6><span class="badge-pill badge-primary text-white">@lang('Send to all contacts')</span></h6>
        @else
        <h6><span class="badge-pill badge-primary text-white">@lang('Send to '){{ array_get($email, 'send_number_of_emails', 0) }}</span></h6>
        @endif
        <h6><span class="badge-pill badge-primary text-white">@lang('Do not send within number of days'): {{ array_get($email, 'do_not_send_within_number_of_days', 5) }}</span></h6>
    </div>

    <div class="card-body">
        <hr/>
        <h2>@lang('Step 4') <small>@lang('Tag contacts based on actions')</small></h2>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table">
            <thead>
            <tr>
                <th><span class="fa fa-play"></span> @lang('Action')</th>
                <th><span class="fa fa-tag"></span> @lang('Tag')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($actions as $action)
                <tr>
                    <td>{{ array_get($action, 'event') }}</td>
                    <td>{{ array_get($action, 'tag') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-body">
        <hr/>
        <h2>@lang('Step 5') <small>@lang('Email tags')</small></h2>
        <h6><span class="fa fa-tags"></span> @lang("Email's Tags")</h6>
        <div class="row">
            <div class="col-sm-6">
                <p>@lang('Included')</p>
                <select multiple class="form-control">
                    @foreach($email->includeTags as $tag)
                    @if($loop->first)
                    <option value="{{ array_get($tag, 'id') }}" selected="">{{ array_get($tag, 'name') }}</option>
                    @else
                    <option value="{{ array_get($tag, 'id') }}">{{ array_get($tag, 'name') }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6">
                <p>@lang('Excluded')</p>
                <select multiple class="form-control">
                    @foreach($email->excludeTags as $tag)
                    @if($loop->first)
                    <option value="{{ array_get($tag, 'id') }}" selected="">{{ array_get($tag, 'name') }}</option>
                    @else
                    <option value="{{ array_get($tag, 'id') }}">{{ array_get($tag, 'name') }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card-body">
        <hr/>
        <div class="row">
            <div class="col-sm-6">
                <p>@lang('Contacts') @lang('who <strong>will</strong> receive this email')</p>
                <select multiple class="form-control">
                    @foreach($contacts as $contact)
                    <option value="{{ array_get($contact, 'id') }}" {{ $loop->first ? 'selected=""' : '' }}>
                        {{ array_get($contact, 'first_name') }}&nbsp;
                        {{ array_get($contact, 'last_name') }}&nbsp;
                        ({{ array_get($contact, 'email_1') }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6">
                    <p>@lang('Contacts') @lang('who <strong>will not</strong> receive this email')</p>
                <select multiple class="form-control">
                    @foreach($contacts_not_included as $contact)
                    <option value="{{ array_get($contact, 'id') }}" {{ $loop->first ? 'selected=""' : '' }}>
                        {{ array_get($contact, 'first_name') }}&nbsp;
                        {{ array_get($contact, 'last_name') }}&nbsp;


                        @if(array_get($email, 'do_not_send_to_previous_receivers') && count(array_where(array_pluck(array_get($contact, 'tags', []), 'id'), function($value, $key) use($exclude_lists_tags){
                            return in_array($value, $exclude_lists_tags);
                        })) == 0 && count(array_where(array_pluck(array_get($contact, 'tags', []), 'id'), function($value, $key) use($exclude_email_tags){
                            return in_array($value, $exclude_email_tags);
                        })) == 0)
                            (@lang('Excluded due this contact already received this email'))
                        @elseif ( !empty(array_get($contact, 'email_1') && is_null($list)) && count(array_where(array_pluck(array_get($contact, 'tags', []), 'id'), function($value, $key) use($exclude_email_tags){
                            return in_array($value, $exclude_email_tags);
                        })) > 0)
                            (@lang('Excluded by email\'s tags'))
                        @elseif ( !empty(array_get($contact, 'email_1') && !is_null($list)) && count(array_where(array_pluck(array_get($contact, 'tags', []), 'id'), function($value, $key) use($exclude_lists_tags){
                            return in_array($value, $exclude_lists_tags);
                        })) > 0)
                            (@lang('Excluded by list\'s tags'))
                        @else
                            (@lang('No email'))
                        @endif
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card-footer">&nbsp;</div>
</div>
@push('scripts')
<script type="text/javascript">
    (function(){
        var top = 84;
        $(window).scroll(function () {
            var y = $(this).scrollTop();

            var button = $('#floating-buttons');
            if (y >= top) {
                button.css({
                    'position': 'fixed',
                    'top': '60px',
                    'right': '51px',
                    'z-index': '99'
                });
            } else {
                button.removeAttr('style')
            }
        });
})();
</script>
@endpush()

@endsection
