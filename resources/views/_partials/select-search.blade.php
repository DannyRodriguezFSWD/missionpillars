<div class="dropdown">
    <button class="btn btn-light bg-white dropdown-toggle" type="button" id="select-search-dropdown-{{ $name }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        @if ($data)
        {{ $data }}
        @else
        {{ $label }}
        @endif
    </button>
    <div class="dropdown-menu" aria-labelledby="select-search-dropdown-{{ $name }}">
        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <input type="text" class="form-control" placeholder="Search" onkeyup="customDelay(customSelectSearch(this, '{{ $name }}'), 500)">
        </div>

        <div id="select-search-container-{{ $name }}" class="overflow-auto" style="max-height: 300px;">
            @foreach ($options as $key => $val)
            <a class="dropdown-item @if ($val === $data) active @endif" data-key="{{ $key }}" data-val="{{ $val }}" data-input="{{ $name }}" onclick="customSelectSearchOption(this, '{{ $name }}')">
                {{ $val }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<input type="hidden" name="{{ $name }}" value="{{ $data }}" />
