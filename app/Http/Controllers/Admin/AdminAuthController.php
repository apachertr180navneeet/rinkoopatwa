<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Mail, DB, Hash, Validator, Session, File,Exception;

class AdminAuthController extends Controller
{
    
    public function index()
    {
        try{
            if(Auth::user()) {
                $user = Auth::user();
                if($user->role == "admin") {
                    return redirect()->route('admin.dashboard');
                }else{
                    return back()->with("error","Opps! You do not have access this");
                }
            }else{
                return redirect()->route('admin.login');
            }

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    

    public function login()
    {
        return view("admin.auth.login");
    }

    public function registration()
    {
        return view("admin.auth.registration");
    }

    public function postLogin(Request $request)
    {
        try{
            $request->validate([
                "email" => "required",
                "password" => "required",
            ]);
            $user = User::where('role','admin')->where('email',$request->email)->first();
            if($user){
                $credentials = $request->only("email", "password");
                if(Auth::attempt([
                        'email' => $request->email,
                        'password' => $request->password,
                        'role' => function ($query) {
                            $query->where('role','admin');
                        }
                    ]))
                {
                    return redirect()->route("admin.dashboard")->with("success", "Welcome to your dashboard.");
                }
                return back()->with("error","Invalid credentials");
            }else{
                return back()->with("error","Invalid credentials");
            }

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function postRegistration(Request $request)
    {
        $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|min:6",
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("admin.dashboard")->with("success","Great! You have Successfully loggedin");
    }

    public function create(array $data)
    {
        return User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
        ]);
    }

    public function showForgetPasswordForm()
    {
        return view("admin.auth.forgot-password");
    }

    public function submitForgetPasswordForm(Request $request)
    {
        try{
            $request->validate([
                "email" => "required|email|exists:users",
            ]);

            $token = Str::random(64);

            DB::table("password_resets")->insert([
                "email" => $request->email,
                "token" => $token,
                "created_at" => Carbon::now(),
            ]);

            $new_link_token = url("admin/reset-password/" . $token);
            Mail::send("admin.email.forgot-password",["token" => $new_link_token, "email" => $request->email],
                function ($message) use ($request) {
                    $message->to($request->email);
                    $message->subject("Reset Password");
                }
            );
            return redirect()->route("admin.login")->with("success","We have e-mailed your password reset link!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    
    }

    public function showResetPasswordForm($token)
    {
        try{    
            $user = DB::table("password_resets")->where("token", $token)->first();
            $email = $user->email;
            return view("admin.auth.reset-password", ["token" => $token,"email" => $email,]);
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function submitResetPasswordForm(Request $request)
    {
        try{
            $request->validate([
                "email" => "required|email|exists:users",
                "password" => "required|string|min:6|confirmed",
                "password_confirmation" => "required",
            ]);

            $updatePassword = DB::table("password_resets")->where(["email" => $request->email,"token" => $request->token])->first();

            if (!$updatePassword) {
                return back()->withInput()->with("error", "Invalid token!");
            }

            $user = User::where("email", $request->email)->update(["password" => Hash::make($request->password)]);

            DB::table("password_resets")->where(["email" => $request->email])->delete();

            return redirect()->route("admin.login")->with("success","Your password has been changed successfully!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function changePassword()
    {
        return view("admin.auth.change-password");
    }

    public function updatePassword(Request $request)
    {
        try{
            $request->validate([
                "old_password" => "required",
                "new_password" => "required|confirmed",
            ]);
            #Match The Old Password
            if (!Hash::check($request->old_password, auth()->user()->password)) {
                return back()->with("error", "Old Password Doesn't match!");
            }
            #Update the new Password
            User::whereId(auth()->user()->id)->update([
                "password" => Hash::make($request->new_password),
            ]);
            return back()->with("success", "Password changed successfully!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    

    public function logout()
    {
        try{
            Session::flush();
            Auth::logout();
            return redirect()->route("admin.login")->withSuccess('Logout Successful!');
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function adminProfile()
    {
        try{
            $user = Auth::user();
            return view("admin.auth.profile", compact("user"));

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function updateAdminProfile(Request $request)
    {
        try
        {
            $user = Auth::user();
            $data = $request->all();
            $validator = Validator::make($data,[
                "first_name" => "required",
                "last_name" => "required",
                "phone" => "required|min:9|unique:users,phone," .$user->id,
                "email" => "required|email|unique:users,email," . $user->id,
                "avatar" => "sometimes|image|mimes:jpeg,jpg,png|max:5000"
            ]);
            
            if($validator->fails()) {
                return redirect()->back()->withInput($request->all())->withErrors($validator->errors());
            }
            
            if($request->file("avatar")) {
                $file = $request->file("avatar");
                $filename = time() . $file->getClientOriginalName();
                $folder = "uploads/user/";
                $path = public_path($folder);
                if (!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $file->move($path, $filename);
                $user->avatar = $folder . $filename;
            }
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->full_name = $request->first_name . " " . $request->last_name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->save();
            return redirect()->back()->with("success", "Profile update successfully!");
        }
        catch (Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }
    }

    public function adminDashboard()
    {
        return view("admin.dashboard.index");
    }


}
