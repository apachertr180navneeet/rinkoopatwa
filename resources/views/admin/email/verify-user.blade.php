<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing:border-box;">
    <tbody>
        <tr>
            <td align="left" style="">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0;padding:0;width:100%">
                    <tbody>
                        <tr>
                            <td width="100%" cellpadding="0" cellspacing="0" style="">
                                <table align="left" cellpadding="0" cellspacing="0" role="presentation">
                                    <tbody>
                                        <tr align="left">
                                            <td align="left">
                                                <p>Greetings from <strong>{{ config('app.name') }}.</strong></p>
                                                <p>To verify this email address belongs to you,enter the code below on the email verification page:</p>
                                                <p><b>{{$code}}</b></p>
                                                <p>This code will expire two hour after this email was sent.</p> 
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>