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
                                                <h3>User Enquiry From <strong>{{ config('app.name') }}</strong> Contact Form.</h3>
                                                <p><strong>Name</strong> : {{$contact_user->name}}</p>
                                                <p><strong>Email</strong> : {{$contact_user->email}}</p>
                                                <p><strong>Message</strong> : {{$contact_user->message}}</p>
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