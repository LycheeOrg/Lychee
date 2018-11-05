<?php

namespace App\Http\Controllers;


use App\Album;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SharingController extends Controller
{
    public function list_sharing(Request $request) {

        if (Session::get('UserID') == 0)
        {
            $shared = DB::table('user_album')
                ->select('user_album.id', 'user_id', 'album_id', 'username', 'title')
                ->join('users','user_id','users.id')
                ->join('albums','album_id','albums.id')
                ->orderBy('title','ASC')
                ->orderBy('username', 'ASC')
                ->get();

            $albums = Album::select('id', 'title')->orderBy('title', 'ASC')->get();
            $users = User::select('id' , 'username')->orderBy('username','ASC')->get();
        }
        else
        {
            $id = Session::get('UserID');
            $shared = DB::table('user_album')
                ->select('user_album.id', 'user_id', 'album_id', 'username', 'title')
                ->join('users','user_id','users.id')
                ->join('albums','album_id','albums.id')
                ->where('albums.owner_id', '=', $id)
                ->orderBy('title','ASC')
                ->orderBy('username', 'ASC')
                ->get();

            $albums = Album::select('id', 'title')->where('owner_id', '=', $id)->orderBy('title', 'ASC')->get();
            $users = User::select('id' , 'username')->orderBy('username','ASC')->get();
        }
        return ['shared' => $shared, 'albums' => $albums, 'users' => $users];
    }
}