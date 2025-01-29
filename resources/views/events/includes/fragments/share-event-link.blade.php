<div class="form-group">
    <span class="fa fa-share-alt"></span>
    {{ Form::label('share', __('Share URL')) }}
    <div class="input-group">
        @if(isset($split))
        {{ Form::text('share', route('events.share', array_get($split, 'uuid')), ['class' => 'form-control', 'readonly' => true, 'id' => 'share-event-link']) }}
        @else
        {{ Form::text('share', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'share-event-link']) }}
        {{ Form::hidden('link', route('events.share', ':id:')) }}
        @endif
        <span class="input-group-append">
            <button onclick="copy('share-event-link')" type="button" class="btn btn-outline-primary" type="button">
                Copy URL
            </button>
        </span>
    </div>
    
</div>