@extends('layouts.children-checkin')

@section('content')

<div class="row no-print">
    <div class="col-sm-12">
        <a href="{{ route('child-checkin.index') }}" class="btn btn-success btn-lg btn-block">
            <span class="icon icon-reload"></span> @lang('Start Over')
        </a>
    </div>
</div>

<div class="container children no-print">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('message') }}
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('message') }}
            </div>
            @endif
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-12 titles">
            <h1>{{ $tenant->organization }}</h1>
            <h3>@lang('Step 3: Select your child')</h3>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-4">
            <a href="{{ route('child-checkin.relative.create', ['id' => $contact->id]) }}" style="text-decoration: none;">
                <div class="card card-inverse card-success">
                    <div class="card-body">
                        <div class="h1 text-muted text-right mb-4">
                            <i class="icon-user-follow"></i>
                        </div>
                        <div class="h4 mb-0">@lang('Add New Relative')</div>
                        <small class="text-muted text-uppercase font-weight-bold">@lang("To ") {{ $tenant->organization }}</small>
                    </div>
                </div>
            </a>
        </div>
        @foreach($allChildren as $child)
        <div class="col-sm-4">
            <a href="#" class="child-card" style="text-decoration: none;" data-toggle="modal" data-target="#confirm-modal">
                <div class="card card-inverse card-primary">
                    <div class="card-body">
                        <div class="h1 text-muted text-right mb-4">
                            <i class="icon-user"></i>
                        </div>
                        <div class="h4 mb-0">
                            <span>{{ $child->first_name }}</span> 
                            <span>{{ $child->last_name }}</span>
                        </div>
                        <small class="text-muted text-uppercase font-weight-bold">{{ $tenant->organization }}</small>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
{{-- <div class="print sticker" style="width: 2.35in; margin: 0 auto;"> --}}
{{-- <div class="print sticker" style="width: 3.5in; margin: 0; padding: 0 .25in"> --}}
<div class="print sticker" style="margin: auto; padding: 0 .2in; font-size: 1rem;">
    <style>
        p, h5 {
            margin: 0;
            padding: 0;
        }
        
        @media print {
            @page {
                size: landscape;
                margin: 0 !important;
            }
            
            #confirm-modal {
                display: none !important;;
            }
            
            .print.sticker {
                color: black !important;
            }
            
            @media print and (-webkit-min-device-pixel-ratio:0) {
                .print.sticker {
                    color: black !important;
                    -webkit-print-color-adjust: exact;
                }
                
            }
        }
    </style>
    <div>
        <p style="padding-top: 5px;">{{ $tenant->organization }}</p>
        <h5 class="name">
            <span class="first_name">&nbsp;</span>
            <small class="last_name">&nbsp;</small>
        </h5>
        <p>{{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }} <small class="id"></small></p>
        <small class="date">&nbsp;</small>
    </div>
    <footer></footer>
    <div>
        <p style="padding-top: 5px;">{{ $tenant->organization }}</p>
        <h5 class="name">
            <span class="first_name">&nbsp;</span>
            <small class="last_name">&nbsp;</small>
        </h5>
        <p>{{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }} <small class="id"></small></p>
        <small class="date">{{ date('l jS \of F Y h:i A') }}</small>
    </div>
    <footer></footer>
</div>

<div class="modal fade" id="confirm-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Print')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>@lang('Print for another child')</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('Yes')</button>
                <a href="{{ route('child-checkin.index') }}" class="btn btn-secondary">@lang('No')</a>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function(){
        $('.child-card').on('click', function(e){
            e.preventDefault();
            var timestamp = new Date().getTime();
            var datetimestring = new Date().toLocaleDateString('en-us', {weekday: 'long', month: 'long', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit'})
            
            $('.print.sticker').find('small.id').html(timestamp);
            $('.print.sticker').find('.date').html(datetimestring)
            $('.print.sticker').find('.name .first_name').html( $(this).find('div.h4 span:first').html() );
            $('.print.sticker').find('.name .last_name').html( $(this).find('div.h4 span:last').html() );
            
            $('body').css('background-image', 'none');
            $('body').css('background-color', 'white');
            
            window.print();
            
            $('body').css('background-image', 'url({{ asset('img/prodotti-86888-reld1f6e99661434d0891c78e9e6d360d34.jpg') }})');
            
            // The following code opens up a new window, prints and then closes
            // w = window.open();
            // w.document.writeln('<style media="print">button {display: none}</style>')
            // w.document.writeln('<button type=button onclick="window.close()">Continue</button>')
            // w.document.writeln($('.print.sticker').get(0).outerHTML)
            // w.print()
            // w.onafterprint(w.close())
        });
    });
</script>
@endpush
