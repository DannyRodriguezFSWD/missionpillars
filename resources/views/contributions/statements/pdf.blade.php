<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>@lang('Print Communication')</title>
        
        <style type="text/css">
            html, body {
                margin: 0px;
                padding: 0px;
                padding-top: 25px;
                background: transparent;
                background-color: transparent;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 0.9em;
                color: #263238;
            }
            .container {
                /* Page margins - Everywhere else margin and padding is 0 in this view */
                padding-left: 0.5in;
                padding-right: 0.5in;
                
                width: 100%;
                max-width: 100%;
                margin: 0 auto;
                box-sizing: border-box;
            }
            .cover{
                padding-top: 350px;
                padding-bottom: 0px;
                text-align: center;
            }
            .page-break{
                page-break-after: always;
            }
            table, tr, td {border: 1px solid #000;}
            table, tr, td, th{ border: none !important; }
            table, tr, td, p { margin: 5px 0; }
            
            .table-item-list {
                width: 100%;
                max-width: 100%;
                margin-bottom: 1rem;
            }
            
            .table-item-list th {
                padding: .60rem;
                text-align: left;
                border-bottom: none;
            }
            
            .table-item-list td {
                padding: .40rem;
                border-top: 1px solid #dee2e6;
            }
            
            @font-face {
                font-family: 'comic sans ms';
                src: url({{ storage_path('fonts/COMIC.TTF') }}) format("truetype");
                font-weight: normal; 
                font-style: normal; 
            }
            
            @font-face {
                font-family: 'arial black';
                src: url({{ storage_path('fonts/ariblk.ttf') }}) format("truetype");
                font-weight: normal; 
                font-style: normal; 
            }
            
            @font-face {
                font-family: 'book antiqua';
                src: url({{ storage_path('fonts/BKANT.TTF') }}) format("truetype");
                font-weight: normal; 
                font-style: normal; 
            }
        </style>
        
        
    </head>
    <body>
        

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

        

    </body>
</html>
