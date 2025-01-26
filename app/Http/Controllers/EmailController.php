<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendEmail;
use Illuminate\Support\Facades\Auth;

//sending email controller

class EmailController extends Controller
{
    public function sendUserEmail(Request $request){
        $user = Auth::user(); // Get currently authenticated user

        // Get current user email
        $emailData = [
            "to" => $user->email,
            "subject" => "This is an email",
            "body" => "Hello :)"
        ];

        if($request->has('mailable')){
            $emailData['mailable'] = $request->mailable;
        }

        return response()->json(["message"=>"Email dispatched to queue"]);
    }
}
