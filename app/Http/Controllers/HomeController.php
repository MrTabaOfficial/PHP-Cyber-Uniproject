<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if(auth::id())
        {
            $usertype=Auth()->user()->role;
            if($usertype=='user')
            {
                return view('dashboard');
            }
            else if($usertype=='admin')
            {
                $users = User::all();
                return view('adminpage', compact('users'));
            }
            else
            {
                return redirect()->back();
            }
        }
    }
}
