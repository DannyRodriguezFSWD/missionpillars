<tr>
    <td style="border-top: 1px solid #EDEFF2;">
        <table class="table-footer" align="center" cellpadding="0" cellspacing="0">
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
