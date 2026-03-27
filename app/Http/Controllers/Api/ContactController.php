<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Mail, Hash, File, Auth, DB, TimeHelper, Exception, Session, Redirect, Validator ,Helper;
use Carbon\Carbon;
use App\Models\Contact;



class ContactController extends Controller
{
    public function submitContact(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ]);
        
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ],200);
        }
        
        try{
            $contact = new Contact();
            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->message = $request->message;
            $contact->save();
            $contactId = $contact->id;
            $contact_details = Contact::where('id', $contactId)->first();
            return response()->json([
                'status' => true,
                'message' => 'Thank you for getting in touch!',
            ],200);
        }catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],200);
        }
    }
}
