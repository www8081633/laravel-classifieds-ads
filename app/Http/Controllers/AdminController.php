<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function AdminDashboard(){
        return view('admin.index');
    }
    public function AdminLogout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    public function AdminLogin(){
        return view('admin.admin_login');
    }// End Method

    public function AdminProfile(){
        $id=Auth::user()->id;
        $profileData=User::find($id);
        return view('admin.admin_profile_view',compact('profileData'));
    }

    public function AdminProfileStore(Request $request){
        $id=Auth::user()->id;
        $data=User::find($id);
        $data->username=$request->username;
        $data->name=$request->name;
        $data->email=$request->email;
        $data->phone=$request->phone;
        $data->address=$request->address;
        if($request->file('photo')){
            $file=$request->file('photo');
            @unlink(public_path('upload/admin_images/'.$data->photo));
            $filename=date('YmdHis').'.'.$file->getClientOriginalExtension();
            $file->move(public_path('upload/admin_images'),$filename);
            $data['photo']=$filename;
        }
        $data->save();
        $notice=[
            'message'=>'Admin Profile Update Successfully',
            'alert-type'=>'success'
        ];
        return redirect()->back()->with($notice);
    }

    public function AdminChangePassword(){
        $id=Auth::user()->id;
        $profileData=User::find($id);
        return view('admin.admin_change_password',compact('profileData'));
    }

    public function AdminUpdatePassword(Request $request){
        $request->validate(
            ['old_password'=>'required','new_password'=>'required|confirmed']
        );
        if(!Hash::check($request->old_password,auth::user()->password)){
            $notice=[
                'message'=>'Old Password Does not Match!',
                'alert-type'=>'error'
            ];
            return back()->with($notice);
        }

        User::whereId(auth()->user()->id)->update(['password'=>Hash::make($request->new_password)]);
        $notice=[
            'message'=>'Password Change Successfully!',
            'alert-type'=>'success'
        ];
        return back()->with($notice);
    }
}