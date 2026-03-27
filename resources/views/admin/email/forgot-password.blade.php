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
                                                @if(isset($email))
                                                    <p>Hi : {{$email}}</p>
                                                @else
                                                    <p>Hi : {{$name}}</p>
                                                @endif
                                                <h3>Welcome to {{ config('app.name') }}.</h3>
                                                @if(isset($token))
                                                    <p>Please click this link to create new password : <strong><a href="{{$token}}">Click Here</a></strong></p>
                                                @else
                                                    <p> Your forgot password 6 digits code is : <strong>{{$code}}</strong></p>
                                                    <p>Note : This code is valid for 2 hour only.</p>
                                                @endif
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
