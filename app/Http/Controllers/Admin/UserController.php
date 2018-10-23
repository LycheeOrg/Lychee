<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\User;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware([]);
    }


    public function list() {
        $users = User::all();
        return $users;
//        return view('admin.users',['users' => $users]);
    }

}