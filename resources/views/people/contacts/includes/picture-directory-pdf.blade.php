<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        @isset($group)
        <title>{{ array_get($group, 'name') }} - @lang('Picture Directory')</title>
        @else
        <title>@lang('Picture Directory')</title>
        @endisset
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
        
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
                padding-bottom: 0.125in;
                
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
            
            .card {
                position: relative;
                display: flex;
                flex-direction: column;
                min-width: 0;
                margin-bottom: 1rem;
                word-wrap: break-word;
                background-clip: border-box;
                border: 1px solid;
                border-radius: 0.25rem;
                background-color: red;
                border-color: #c8ced3;
            }
            
            .card-body {
                flex: 1 1 auto;
                min-height: 1px;
                padding: 1.25rem;
            }
            
            .text-center {
                text-align: center !important;
            }
            
            .img-thumbnail {
                padding: 0.25rem;
                background-color: #ebedef;
                border: 1px solid #c4c9d0;
                border-radius: 0.25rem;
            }
            
            .rounded-circle {
                border-radius: 50% !important;
            }
            
            .mb-0, .my-0 {
                margin-bottom: 0 !important;
            }
            
            .fa {
                display: inline;
                font-style: normal;
                font-variant: normal;
                font-weight: normal;
                font-size: 14px;
                line-height: 1;
                font-family: FontAwesome;
                font-size: inherit;
                text-rendering: auto;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
        </style>
    </head>
    <body style="background-color: #e4e5e6;">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="document-page text-center cover">
                        <h2>{{ array_get(auth()->user(), 'tenant.organization') }}</h2>
                        <br>
                        @isset($group)
                        <h3>{{ array_get($group, 'name') }} - @lang('Picture Directory')</h3>
                        @else
                        <h3>@lang('Picture Directory')</h3>
                        @endisset
                    </div>
                </div>
            </div>
            <div class="page-break">&nbsp;</div>

            <table style="width: 100%;">
                <tr>
                @for ($i = 1; $i <= $contacts->count(); $i++)
                    <td style="width: 33%; vertical-align: top;">
                        <div class="card" style="width: 95%;">
                            <div class="card-body" style="height: 270px; background-color: white;">
                                <p class="text-center">
                                    <img class="img-thumbnail rounded-circle" style="width: 100px; height: 100px;" src="{!! $contacts[$i-1]->profile_image_src !!}" />
                                </p>
                                <h4>{!! $contacts[$i-1]->full_name !!}</h4>
                                @if ($contacts[$i-1]->email_1)
                                <p class="mb-0">
                                    <i class="fa fa-envelope" style="color: #ffc107;"></i> {{ $contacts[$i-1]->email_1 }}
                                </p>
                                @endif
                                @if ($contacts[$i-1]->cell_phone)
                                <p class="mb-0">
                                    <i class="fa fa-phone" style="color: #63c2de;"></i> {{ $contacts[$i-1]->cell_phone }}
                                </p>
                                @endif
                                @if ($contacts[$i-1]->full_address)
                                <p><i class="fa fa-map-marker" style="color: #4dbd74;"></i> {{ $contacts[$i-1]->full_address }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                @if ($i % 3 === 0)
                </tr><tr>
                @endif
                @endfor
            </table>
        </div>
    </body>
</html>
