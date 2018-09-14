<?php

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    function setLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required']);

        $oldPassword = $request->has('oldPassword') ? $request['oldPassword'] : '';

        if ($oldPassword === Configs::get(false)['password'] || Hash::check($oldPassword, Configs::get(false)['password'])) {
            Configs::set('username',bcrypt($request['username']));
            Configs::set('password',bcrypt($request['password']));
            return 'true';
        }


        return Response::error('Current password entered incorrectly!');
    }

    function setSorting(Request $request)
    {
        $request->validate([
            'typeAlbums' => 'required|string',
            'orderAlbums' => 'required|string',
            'typePhotos' => 'required|string',
            'orderPhotos'  => 'required|string'
            ]);

        Configs::set('sortingPhotos_col',$request['typePhotos']);
        Configs::set('sortingPhotos_order',$request['orderPhotos']);
        Configs::set('sortingAlbums_col',$request['typeAlbums']);
        Configs::set('sortingAlbums_order',$request['orderAlbums']);

        if('typeAlbums' == 'max_takestamp' or 'typeAlbums' == 'min_takestamp')
        {
            Album::reset_takestamp();
        }

        return 'true';
    }
}
