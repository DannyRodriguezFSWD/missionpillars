@if ($paginator->hasPages())
    <ul class="pagination d-flex justify-content-center">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link d-none d-md-block">First</span>
                <span class="page-link fa fa-angle-double-left d-block d-md-none"></span>
            </li>
            <li class="page-item disabled">
                <span class="page-link d-none d-md-block">Previous</span>
                <span class="page-link fa fa-angle-left d-block d-md-none"></span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link d-none d-md-block" href="{{ $paginator->url(1) }}">First</a>
                <a class="page-link fa fa-angle-double-left d-block d-md-none" href="{{ $paginator->url(1) }}"></a>
            </li>
            <li class="page-item">
                <a class="page-link d-none d-md-block" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
                <a class="page-link fa fa-angle-left d-block d-md-none" href="{{ $paginator->previousPageUrl() }}" rel="prev"></a>
            </li>
        @endif
        <li class="page-item">
            <input type="text" value="{{$paginator->currentPage()}}" class="form-control" id="paginator_page_input">
        </li>
        <li class="page-item disabled">
            <span class="page-link" id="pagination_last_page_indicator">of {{$paginator->lastPage()}}</span>
        </li>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link fa fa-angle-right d-block d-md-none" href="{{ $paginator->nextPageUrl() }}" rel="next"></a>
                <a class="page-link d-none d-md-block" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            </li>
            <li class="page-item">
                <a class="page-link fa fa-angle-double-right d-block d-md-none" href="{{ $paginator->url($paginator->lastPage()) }}"></a>
                <a class="page-link d-none d-md-block" href="{{ $paginator->url($paginator->lastPage()) }}">Last</a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link d-none d-md-block">Next</span>
                <span class="page-link fa fa-angle-right d-block d-md-none"></span>
            </li>
            <li class="page-item disabled">
                <span class="page-link d-none d-md-block">Last</span>
                <span class="page-link fa fa-angle-double-right d-block d-md-none"></span>
            </li>
        @endif
    </ul>
@endif

@push('scripts')
    <script>
        (function () {
            $('#pagination_last_page_indicator').parent().on('click', function (e) {
                $('#paginator_page_input').focus()
            })
            $('#paginator_page_input').on('keypress', function (e) {
                if (e.keyCode == 13) {
                    let url = new URL(window.location.href)
                    url.searchParams.set('page', e.target.value);
                    window.location.href = url;
                }
            })
        })()
    </script>
@endpush
@push('styles')
    <style>
        #paginator_page_input {
            text-align: center;
            padding: 0px;
            width: 55px;
            height: 35px;
            border-radius: 0;
            border-left: 0;
            border-color: #c4c9d0;
            box-shadow: inset 1px 2px #888;
        }

        #pagination_last_page_indicator{
            border-left: 0;
            box-shadow: inset 0 2px #888;
        }

        .page-item > a.page-link {
            text-decoration: none;
            color: darkslategray;
        }
    </style>
@endpush