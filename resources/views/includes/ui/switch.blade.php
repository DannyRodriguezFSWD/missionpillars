<label class="c-switch-sm mb-0 c-switch-label c-switch-success">
    <input type="checkbox" value="1" id="{{$name}}" name="{{$name}}" class="c-switch-input" @if(!is_null($checked) && $checked) checked @endif>
    <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
</label>
