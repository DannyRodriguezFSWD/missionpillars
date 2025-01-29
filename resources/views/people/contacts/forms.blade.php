@extends('layouts.app')

@section('content')


    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    @include('people.contacts.includes.card-header')
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="forms-info">
                                @include('people.contacts.includes.forms-info')
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <!--/.col-->

    </div>
    

@include('people.contacts.includes.delete-address-modal')
@push('scripts')
<script type="text/javascript">
    (function () {
        
        var top = 35;
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
