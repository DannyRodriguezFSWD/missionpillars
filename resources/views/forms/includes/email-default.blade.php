<table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <table class="inner-body" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <p>
                            Dear [[ CONTACT.FIRST_NAME ]] [[ CONTACT.LAST_NAME ]]<br>
                            Thank you very much for submitting the <b>[[ FORM.NAME ]]</b> form.
                        </p>
                        <div class="email-default-payments-yes d-none">
                            <p>You will get another email when you complete your payment.</p>
                            <p>
                                You can make your payment here
                                [[ PAYMENT_FORM_LINK ]].
                            </p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>&nbsp;</p>
                        <p>Sincerely<br>{{ array_get(auth()->user()->tenant, 'organization') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
