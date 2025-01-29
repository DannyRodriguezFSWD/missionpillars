<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    @if($includePublicLink)
        <meta property='og:title' content='{{ array_get($item, 'subject') }}'/>
        <meta property='og:image' content=''/>
        <meta property='og:description' content='{{ substr(strip_tags(array_get($item, 'content')),0,100) . '...' }}'/>
        <meta property='og:url' content='{{ $shareLink }}' />
    @endif

    @if(!isset($textOnly) || $textOnly === false)
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
            
            .content-cell {
                padding: 0 !important;
            }
            
            .content-cell-sm {
                padding: 0 !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }

        /* Base */

        body, body *:not(html):not(style):not(br):not(tr):not(code) {
            box-sizing: border-box;
        }

        body {
            background-color: #f5f8fa;
            color: #74787E;
            height: 100%;
            hyphens: auto;
            line-height: 1.4;
            margin: 0;
            -moz-hyphens: auto;
            -ms-word-break: break-all;
            width: 100% !important;
            -webkit-hyphens: auto;
            -webkit-text-size-adjust: none;
            word-break: break-all;
            word-break: break-word;
        }


        a {
            color: #3869D4;
        }

        a img {
            border: none;
        }



        img {
            max-width: 100%;
        }

        /* Layout */

        .wrapper {
            margin: 0;
            padding: 0;
            width: 100%;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }

        .content {
            margin: 0;
            padding: 0;
            width: 100%;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }

        /* Header */

        .header {
            padding: 25px 0;
            text-align: center;
        }

        .header a {
            color: #bbbfc3;
            font-size: 19px;
            font-weight: bold;
            text-decoration: none;
            text-shadow: 0 1px 0 white;
        }

        /* Body */

        .body {
            background-color: #FFFFFF;
            border-bottom: 1px solid #EDEFF2;
            border-top: 1px solid #EDEFF2;
            margin: 0;
            padding: 0;
            width: 100%;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }

        .inner-body {
            width: 570px;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 570px;
        }


        /* Footer */

        .footer {
            margin: 0 auto;
            padding: 0;
            text-align: center;
            width: 570px;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 570px;
        }

        .footer, .footer a {

            font-size: 12px;
            text-align: center;
        }

        /* Tables */

        .table table {
            margin: 30px auto;
            width: 100%;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }

        .table th {
            border-bottom: 1px solid #EDEFF2;
            padding-bottom: 8px;
        }

        .table td {
            color: #74787E;
            line-height: 18px;
            padding: 10px 0;
        }

        .content-cell {
            padding: 35px;
        }

        .content-cell-sm {
            padding: 15px;
        }

        /* Buttons */

        .action {
            margin: 30px auto;
            padding: 0;
            text-align: center;
            width: 100%;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }
        a.button{
            color: #FFF;
        }
        .button {
            border-radius: 3px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
            color: #FFF;
            display: inline-block;
            text-decoration: none;
            -webkit-text-size-adjust: none;
        }

        .button-blue {
            background-color: #3097D1;
            border-top: 10px solid #3097D1;
            border-right: 18px solid #3097D1;
            border-bottom: 10px solid #3097D1;
            border-left: 18px solid #3097D1;
        }

        .button-green {
            background-color: #2ab27b;
            border-top: 10px solid #2ab27b;
            border-right: 18px solid #2ab27b;
            border-bottom: 10px solid #2ab27b;
            border-left: 18px solid #2ab27b;
        }

        .button-red {
            background-color: #bf5329;
            border-top: 10px solid #bf5329;
            border-right: 18px solid #bf5329;
            border-bottom: 10px solid #bf5329;
            border-left: 18px solid #bf5329;
        }

        .button-orange{
            background-color: #fda428;
            border-top: 10px solid #fda428;
            border-right: 18px solid #fda428;
            border-bottom: 10px solid #fda428;
            border-left: 18px solid #fda428;
        }

        .button-footer {
            display: inline-block;
            background: #848181;
            color: #ffffff;
            font-size: 16px;
            font-weight: normal;
            line-height: 100%;
            margin: 0;
            text-decoration: none;
            text-transform: none;
            padding: 7px 15px;
            mso-padding-alt: 0px;
            border-radius: 24px;
        }

        .table-item-list {
            width: 100%;
            max-width: 100%;
        }

        .table-item-list th {
            padding: .60rem;
            text-align: left;
            border-bottom: none;
        }

        .table-item-list td {
            padding: .60rem;
            border-top: 1px solid #dee2e6;
        }

    </style>
    @endif
</head>
<body>
<?php
//$images = $dom->getElementsByTagName('img');
//$content = $dom->saveHTML($dom->documentElement->lastChild);
//$content = str_replace('<body>', '', str_replace('</body>', '', $content));
$images = html_matchImgTag($content);

foreach (array_get($images, 0) as $img) {
    //$src = $img->getAttribute('src');
    $src = html_matchSrcAttr($img);
    if(strpos($src, 'http') !== false ) continue;

    extract(html_extractMime($src)); // provides $filename and $mime
    if (!$mime) continue;

    $base64 = str_replace($mime, '', $src);
    $bin = base64_decode($base64);

    $cid = $message->embedData($bin, $filename);
    $content = str_replace($src, $cid, $content);
}
?>


<table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0">
            <!--
                        <tr>
                            <td class="header">
                                <a href="{{ $link }}">
                                    {{ array_get($item, 'organization') }}
                    </a>
                </td>
            </tr>
-->

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0">
                        <table class="inner-body" align="center" cellpadding="0" cellspacing="0">
                            <!-- Body content -->
                            @yield('content')
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table class="footer" align="center" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="content-cell-sm" align="center">
                                    &copy; {{ date('Y') }} {{ array_get($tenant, 'organization') }}. All rights reserved.
                                </td>
                            </tr>
                            <tr>
                                <td class="content-cell-sm" align="center" style="padding-top: 0;">
                                    @if(!isset($textOnly) || $textOnly === false)
                                    <a href="{{ env('C2G_MAIN_URL', 'https://www.continuetogive.com') }}" target="_blank">
                                        <img src="{{ url('/img/c2g/logo_poweredby.png') }}" alt="Continue To Give" style="height: 35px; margin-left: 5px; margin-bottom: -13px;"/>
                                    </a>
                                    @else
                                    Powered by Continue To Give
                                    @endif
                                </td>
                            </tr>
                                <tr>
                                    <td class="content-cell-sm">
                                        <p style="text-align: justify; font-size: 12px; color: #aeaeae;">
                                            {{ $reminder }}
                                        </p>
                                        <p style="text-align: center;">
                                            @if($show_unsubscribe)
                                                <a class="button-footer" href="{{ $unsubscribe }}">@lang('Unsubscribe')</a>
                                            @endif
                                            @if($includePublicLink)
                                                <a class="button-footer" href="{{ $publicLink }}">@lang('Web Version')</a>
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @if(!is_null($cancelPledgeLink))
                                <tr>
                                    <td class="content-cell-sm" style="padding-top: 0;">
                                        <p style="text-align: center;">
                                            <a class="button-footer" href="{{ $cancelPledgeLink }}">@lang('Cancel Pledge')</a>
                                        </p>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
