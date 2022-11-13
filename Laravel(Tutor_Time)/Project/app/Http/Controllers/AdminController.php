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

use CreateStudentsTable;

class AdminController extends Controller
{
    //Faiza
    function main()
    {
        if(session()->has('adminusername'))
        {
            return redirect()->route('admin.home');
        }
        return view('mainpage');
    } 
    function home()
    {
        if(session()->has('adminusername'))
        {
            return view('admin.home');
        }
        return redirect()->route('admin.adlogin');
    }
    
    function tlist()
    {
        if(session()->has('adminusername'))
        {
            $Tutors = Tutor::paginate(3);
            return view('admin.TList')
            ->with('Tutors',$Tutors);
        }
        return redirect()->route('admin.adlogin');
        
    }
    function tsearch(Request $req)
    {
        if(session()->has('adminusername'))
        {
            if(isset($_GET['search_tea']))
            {
                $search_text=$_GET['search_tea'];
                $Tutors= DB::table('tutors')->where('name','Like','%'. $search_text.'%')->paginate(2);
                $Tutors->appends($req->all());
                return view('admin.tsearch')
                ->with('Tutors',$Tutors);
            }
            else
            {
                return view('admin.teacherdetails');  
            }
        }
        return redirect()->route('admin.adlogin');  
    }
   

    function slist()
    {
        if(session()->has('adminusername'))
        {
            $Students = Student::paginate(3);

            return view('admin.SList')
            ->with('Students',$Students);
        }
        return redirect()->route('admin.adlogin');
            
    }
  
    function ssearch(Request $req)
    {
        if(session()->has('adminusername'))
        {
            if(isset($_GET['search_stu']))
        {
            $search_text=$_GET['search_stu'];
            $Students= DB::table('students')->where('name','Like','%'. $search_text.'%')->paginate(2);
            $Students->appends($req->all());
            return view('admin.ssearch')
            ->with('Students',$Students);
        }
        else
        {
            return view('admin.studentdetails');  
        }
        }    
        return redirect()->route('admin.adlogin');      
    }
    
  function updateStatus($student_id,$status_code)
    {
        $update_Students = Student::where('student_id',$student_id)->update(['status' => $status_code]);
        
        if($update_Students>0)
        {
            return back();
        }              
    }
    function updatetStatus($tutor_id,$status)
    {
        $update_Tutors = Tutor::where('tutor_id',$tutor_id)->update(['status' => $status]);
        
        if($update_Tutors>0)
        {
            return back();
        }              
    }

    //Adrita
    function adlogin()
    {
        if(session()->has('adminusername'))
        {
            return redirect()->route('admin.home');
        }
        return view('admin.adlogin');
    }

    function adloginSubmit(Request $req)
    {
        $req->validate
        ([
            'username' => 'required',
            'password'=> 'required',
        ] );

        $admin = Admin::where('username',$req->username)->where('password',$req->password)->first();
        $us = array($admin);

        if($us[0] === null)
        {
            return redirect()->route('admin.adlogin');
        }
        else
        {
            $req->session()->put('adminusername', $us[0]->username);
            $req->session()->put('adminpassword', $us[0]->password);
 
            return redirect()->route('admin.home');
        }
    }
    function tutor($tutor_id)
    {
        if(session()->has('adminusername'))
        {
            $Tutors = Tutor::where('tutor_id',$tutor_id)->first();
            return view('admin.teacherdetails')
            ->with('Tutor',$Tutors);
        }
        return redirect()->route('admin.adlogin');
    }
    function student($student_id)
    {
        if(session()->has('adminusername'))
        {
            $Students = Student::where('student_id',$student_id)->first();
        
            return view('admin.studentdetails')
            ->with('Student',$Students);
        }
        return redirect()->route('admin.adlogin');
    }
    function delete($tutor_id)
    {
        if(session()->has('adminusername'))
        {
            $Tutors= Tutor::where('tutor_id',$tutor_id)->delete();
            return back();
        }
        return redirect()->route('admin.adlogin');
        
    }

    function remove($student_id)
    {
        if(session()->has('adminusername'))
        {
            $Students= Student::where('student_id',$student_id)->delete();
            return back();
        }
        return redirect()->route('admin.adlogin');        
    }
   
    function create()
    {
        return view('admin.upload');  
    }

   function index()
   {
      return view('multiple_image');  
   }
   function save(Request $req)
   {
        $this->validate($req,
        [ "pro_pic"=>"required|mimes:jpeg,png,jpg,gif,svg|max:2048"]);
  
     $name =  $req->file('pro_pic')->getClientOriginalName();
     $ext = $req->file('pro_pic')->getClientOriginalExtension();
     $path = "profile_images/";
     $file_name  = time()."_$name";
     $req->file('pro_pic')->storeAs('public/'.$path,$file_name);
       $s1 = new Admin();
       $s1->pro_pic = 'storage/'.$path.$file_name;
       $s1 = Admin::where('username',$req->uname)
       ->update(['pro_pic'=>$req->pro_pic]); 
        session()->flash('msg','File uploaded Successfully');
      
      return back();
    }
 
    function display()
    {
        if(session()->has('adminusername'))
        {
            $admins = admin::all();
 
            return view('admin.upload')
            ->with('admin',$admins);
        }
        return redirect()->route('admin.adlogin');
            
    }
   function del($username)
   {
       if(session()->has('adminusername'))
       {
           $admins= admin::where('username',$username)->delete();
           return back();
       }
       return redirect()->route('admin.adlogin');        
   }

    function logout()
    {
        session()->flush();
        return redirect()->route('admin.adlogin');
    }
}

