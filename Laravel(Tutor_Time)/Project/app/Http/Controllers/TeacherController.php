<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\mail;
use Illuminate\Support\Facades\DB;
use App\Models\Application;
use App\Models\Admin;
use App\Models\Feedback;
use App\Models\Otp;
use App\Models\STC_mapping;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TeacherReview;
use App\Mail\mailHelperClass;


class TeacherController extends Controller
{
    function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }

    function home()
    {
        return view('tutor.home')
        ->with('name',session()->get('name'))
        ->with('email',session()->get('email'))
        ->with('id',session()->get('tutor_id'));
    }

    function myTutorProfile()
    {
        return view('tutor.myprofile')
        ->with('name',session()->get('name'))
        ->with('phone',session()->get('phone'))
        ->with('email',session()->get('email'))
        ->with('gender',session()->get('gender'));
    }

    function editProfile()
    {
        return view('tutor.editprofile')
        ->with('name',session()->get('name'))
        ->with('phone',session()->get('phone'))
        ->with('email',session()->get('email'))
        ->with('gender',session()->get('gender'));
    }

    function editProfileSubmit(Request $req)
    {
        $req->validate
        (
            [
                "name"=>["required","regex:/^[\pL\s]+$/u"],
                "email"=>["required", "email:rfc,dns"],
                "gender"=>["required"],
                "phone"=>["required","regex:/(^(\+88|0088)?(01){1}[3456789]{1}(\d){8})$/"]
            ],
            [
                "name.regex"=> "Name must be alphabetic",
                "email.regex"=> "Email is invalid",
                "phone.regex"=> "Phone number is invalid"
            ]
        );

        $tu = new Tutor();
        $tu = Tutor::where('username', session()->get('username'))->update(['name' => $req->name, 'phone' => $req->phone, 'email' => $req->email, 'gender' => $req->gender]);

        if($tu>0)
        {
            $req->session()->put('email', $req->email);
            $req->session()->put('name', $req->name);
            $req->session()->put('gender', $req->gender);
            $req->session()->put('phone', $req->phone);

            session()->flash('ma','Profile Updated');
            return redirect()->route('tutorprofile');
        }
        else
        {
            session()->flash('ma','Nothing was updated');
            return redirect()->route('tutorprofile');
        }
    }

    function getJobs()
    {
        /*$apps = Application::all();
        $apps = Tutor::join('stc_mappings', 'applications.app_id', '<>', 'stc_mappings.app_id')
        ->select('applications.*')->get();

        return view('tutor.editprofile')
        ->with('apps', $apps);*/

        /*return Application::join('stc_mappings', 'applications.app_id', '=', 'stc_mappings.app_id')
        ->where('stc_mappings.app_id','<>' ,'appications.app_id')
        ->select('applications.*')->get();*/

        $t = Application::all();
        $t = Application::where('cond', 'r')->paginate(1);
        
        return view('tutor.jobboard')
            ->with('tutor',$t);
    }

    function getJobsSubmit($app_id)
    {
        $t = Application::all();
        $t = Application::where('app_id', $app_id)->update(['teacher_id' => session()->get('tutor_id'), 'cond' => 'a']);;
        
        return redirect()->route('getjobs');
    }

    function myJobs()
    {
        $t = Application::all();
        $t = Application::where('teacher_id', session()->get('tutor_id'))->paginate(1);
        
        return view('tutor.myjobs')
            ->with('tutor',$t);
    }

    function myFeedbacks()
    {
        $t = TeacherReview::all();
        $t = TeacherReview::where('tutor_id', session()->get('tutor_id'))->paginate(1);
        
        return view('tutor.myfeedbacks')
            ->with('tutor',$t);
    }

    function editPassword()
    {
        return view('tutor.editpw');
    }

    function editPasswordSubmit(Request $req)
    {
        $req->validate
        (
            [
                "opassword"=>["required"],
                "npassword"=>["required","min:8","regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/"],
                "cpassword"=>["required","same:npassword"]
            ],
            [
                "opassword.required"=> "You must enter the old password",
                "npassword.required"=> "You must enter the new password",
                "cpassword.required"=> "Please confirm your password",
                "npassword.min"=> "Password has to be more than 8 characters",
                "npassword.regex"=> "Password must contain upper case, lower case, number and special 
                characters",
                "cpassword.same"=> "Passwords do not match!"
            ]
        );

        if($req->opassword == session()->get('password'))
        {
            $tu = new Tutor();
            $tu = Tutor::where('username', session()->get('username'))->update(['password' => $req->npassword]);

            if($tu>0)
            {
                session()->flash('ma','Password Updated');
                return redirect()->route('tutorprofile');
            }
            else
            {
                session()->flash('ma','Failed to update password');
                return redirect()->route('tutorprofile');
            }
        }
        else
        {
            session()->flash('ma','Old password does not match. Try again!');
            return redirect()->route('editpw');
        }

        
    }

    function login()
    {
        return view('tutor.login');
    }

    function forgotPassword()
    {
        return view('tutor.forgotpw');
    }

    function forgotPasswordSubmit(Request $req)
    {
        $req->validate(
            [
               "username"=>["required"],
            ],[]
           );

        $t = Tutor::all();
        $t = Tutor::where('username', $req->username)->first();

        if($t)
        {
            $req->session()->put('cemail', $t->email);
            $req->session()->put('cusername', $t->username);

            return redirect()->route('verify');
        }
        else
        {
            session()->flash('m','No such username exists!');
            return redirect()->route('forgotpw');
        }
    }

    function create()
    {
        return view('tutor.create');
    }

    function changePassword()
    {
        return view('tutor.forgotchangepw');
    }

    function loginSubmit(Request $req)
    {
        $req->validate(
            [
               "username"=>"required",
               "password"=>"required"
            ],[]
           );

        $tutors = Tutor::all();
        $tutors = Tutor::where('username',$req->username)->first();

        if($tutors)
        {
        if($tutors->status==1)
        {
            $req->session()->put('tutor_id', $tutors->tutor_id);
            $req->session()->put('email', $tutors->email);
            $req->session()->put('name', $tutors->name);
            $req->session()->put('username', $tutors->username);
            $req->session()->put('gender', $tutors->gender);
            $req->session()->put('phone', $tutors->phone);
            $req->session()->put('password', $tutors->password);
 
            return redirect()->route('thome');            
        }
        else
        {
            session()->flash('ma','Your account is banned');
            return back();
        }
        }
    }

    function createSubmit(Request $req)
    {
        $req->validate
        (
             [
                "name"=>["required","regex:/^[\pL\s]+$/u"],
                "username"=>["required","unique:tutors,username"],
                "email"=>["required"],
                "gender"=>["required"],
                "phone"=>["required","regex:/(^(\+88|0088)?(01){1}[3456789]{1}(\d){8})$/"],
                "password"=>["required","min:8","regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/"],
                "cpassword"=>["required","same:password"]
             ],
             [
                "name.regex"=> "Name must be alphabetic",
                "username.unique"=> "Username has been taken",
                "password.min"=> "Password has to be more than 8 characters",
                "email.regex"=> "Email is invalid",
                "phone.regex"=> "Phone number is invalid",
                "password.regex"=> "Password must contain upper case, lower case, number and special 
                characters",
                "cpassword.same"=> "Passwords do not match!"
             ]
        );

        $req->session()->put('cemail', $req->email);
        $req->session()->put('cname', $req->name);
        $req->session()->put('cusername', $req->username);
        $req->session()->put('cgender', $req->gender);
        $req->session()->put('cphone', $req->phone);
        $req->session()->put('cpassword', $req->password);
        
        return redirect()->route('verify');
    }

    function changePasswordSubmit(Request $req)
    {
        $req->validate
        (
            [
                "password"=>["required","min:8","regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/"],
                "cpassword"=>["required","same:password"]
            ],
            [
                "password.min"=> "Password has to be more than 8 characters",
                "password.regex"=> "Password must contain upper case, lower case, number and special 
                characters",
                "cpassword.same"=> "Passwords do not match!"
            ]
        );

        $cpw = new Tutor();
        $cpw = Tutor::where('username', session()->get('cusername'))->update(['password' => $req->password]);

        if($cpw>0)
        {
            session()->flush();
            return redirect()->route('login');
        }
        else
        {
            return redirect()->route('changepw');
        }
    }

    function verify()
    {
        $otp = rand(100000,999999);

        Mail::to(session()->get('cemail'))->send(new mailHelperClass('Account Verification',$otp));

        $o1 = new Otp();
        $o1->otp = $otp;
        $o1->save();

        return view('tutor.verifymail');
    }
    
    function verifySubmit(Request $req)
    {
        $req->validate(
            [
               "otp"=>["required"],
            ],[]
           );

        $ot = Otp::all();
        $ot = Otp::where('otp', $req->otp)->delete();

        if($ot>0 && session()->has('cgender'))
        {
            $t1 = new Tutor();
            $t1->name = session()->get('cname');
            $t1->username = session()->get('cusername');
            $t1->gender = session()->get('cgender');
            $t1->phone = session()->get('cphone');
            $t1->email = session()->get('cemail');
            $t1->password = session()->get('cpassword');
            $t1->save();
            session()->flush();
            session()->flash('msg1','Account created');
            return redirect()->route('login');
        }
        else if($ot>0)
        {
            return redirect()->route('changepw');
        }
        else
        {
            session()->flash('msg','Wrong OTP code');
            return view('tutor.verifymail');
        }
    }
}