<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>@lang('Print Communication')</title>
        <link href="{{ asset('css/coreui.css') }}" rel="stylesheet">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
        <script src="{{ asset('js/jquery.min.js') }}"></script>

        @if(!isset($download))
        <style type="text/css">
            .document-page{
                background: #FFF;
                margin-top: 10px;
                margin-bottom: 10px;
                padding-top: 4em;
                padding-right: 2em;
                padding-bottom: 4em;
                padding-left: 2em;
                -webkit-box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.75);
                -moz-box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.75);
                box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.75);
            }

            .cover{
                padding-top: 500px;
                padding-bottom: 500px;
            }

            .print-nav-bar{
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                background: rgba(0, 0, 0, 0.8);
                z-index: 9999;
            }
            .print-nav-bar a{
                text-decoration: none !important;
            }

            /*----OVERLAY & spinner----*/
#overlay .spinner {
    vertical-align: middle;
    margin: 0 auto;
    margin-top: 25%;
    width: 50px;
    height: 40px;
    text-align: center;
    font-size: 10px;
}

.spinner > div {
    background-color: #333;
    height: 100%;
    width: 6px;
    display: inline-block;

    -webkit-animation: sk-stretchdelay 1.2s infinite ease-in-out;
    animation: sk-stretchdelay 1.2s infinite ease-in-out;
}

.spinner .rect2 {
    -webkit-animation-delay: -1.1s;
    animation-delay: -1.1s;
}

.spinner .rect3 {
    -webkit-animation-delay: -1.0s;
    animation-delay: -1.0s;
}

.spinner .rect4 {
    -webkit-animation-delay: -0.9s;
    animation-delay: -0.9s;
}

.spinner .rect5 {
    -webkit-animation-delay: -0.8s;
    animation-delay: -0.8s;
}

@-webkit-keyframes sk-stretchdelay {
    0%, 40%, 100% { -webkit-transform: scaleY(0.4) }  
    20% { -webkit-transform: scaleY(1.0) }
}

@keyframes sk-stretchdelay {
    0%, 40%, 100% { 
        transform: scaleY(0.4);
        -webkit-transform: scaleY(0.4);
    }  20% { 
        transform: scaleY(1.0);
        -webkit-transform: scaleY(1.0);
    }
}

.btn{
    cursor: pointer;
}

#overlay{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    z-index: 9999;
    display: none;
}

            @media print {
                body { 
                    background-color: white !important; 
                }
                
                .container{
                    width: 100% !important;
                }
                .cover{
                    padding-top: 350px;
                    padding-bottom: 0px;
                }
                .print-nav-bar{ display: none; }
                table, tr, td, th{ border: none !important; }
            }
        </style>
        @else
        <style type="text/css">
            html, body {
                margin-left: 0px;
                margin-right: 0px;
                background: transparent;
                background-color: transparent;
            }
            .cover{
                padding-top: 350px;
                padding-bottom: 0px;
            }
        </style>
        @endif
        <style type="text/css">
            .page-break{
                page-break-after: always;
            }
            table, tr, td, th{ border: none !important; }
            
            table.table th,
            table.table td {
                padding: 0 !important;
                line-height: inherit !important;
            }
        </style>
    </head>
    <body>
        @if(!isset($download))
        <div class="print-nav-bar">
            <div class="row">
                <div class="col-sm-6">
                    <a href="javascript:history.back()" class="btn btn-link text-white">
                        <span class="fa fa-chevron-left"></span> @lang('Back')
                    </a>
                </div>
                <div class="col-sm-6 text-right">
                    <button id="pdf" class="btn btn-link text-white">
                        <span class="fa fa-file-pdf-o"></span> @lang('Download as PDF')
                    </button>
                </div>
            </div>
        </div>
        @endif

        <div class="container">

            <div class="row">
                <div class="col-sm-12">
                    <div class="document-page text-center cover">
                        {{-- <h1>@lang('Contribution Statements')</h1> --}}
                        @if ($statement->include_transactions)
                            <h3>{{ humanReadableDate($statement->transaction_start_date) }} - {{ humanReadableDate($statement->transaction_end_date) }}</h3>
                        @endif
                        <h4>{{ array_get(auth()->user(), 'tenant.organization') }}</h4>
                    </div>
                </div>
            </div>
            <div class="page-break">&nbsp;</div>
            @include('contributions.statements.includes.content')
        </div>
        <div id="overlay">
            <div class="spinner">
                <div class="rect1"></div>
                <div class="rect2"></div>
                <div class="rect3"></div>
                <div class="rect4"></div>
                <div class="rect5"></div>
                <p style="position: absolute; text-align: center; width: 100%; left:0; ">We are creating your report, wait a minute please.</p>
            </div>
        </div>

        <script type="text/javascript">
            @if (array_get(app('request'), 'download') === 'true' || array_get(app('request'), 'download') === true)
                window.onload = function () {
                    $('#pdf').trigger('click');
                }
            @endif
            
            $(document).ready(function(){
                $('#print').on('click', function(e){
                    window.print();
                });
                
                $('#pdf').on('click', function(){
                    $('#overlay').show();
                    var req = new XMLHttpRequest();
                    req.open("GET", "{{ route('print-mail.pdf', ['uuid' => array_get($statement, 'uuid'), 'id' => array_get($statement, 'id'), 'contact_id' => $contact_id]) }}", true);
                    req.responseType = "blob";

                    req.onload = function (event) {
                        var blob = req.response;
                        var link=document.createElement('a');
                        link.href=window.URL.createObjectURL(blob);
                        link.download="Print_communication.pdf";
                        document.body.appendChild(link);
                        link.click();
                        $('#overlay').hide();
                    };

                    req.send();
                    //window.location.href = "{{ route('print-mail.pdf', ['uuid' => array_get($statement, 'uuid'), 'id' => array_get($statement, 'id'), 'contact_id' => $contact_id]) }}";
                });
            });
        </script>

    </body>
</html>
