<?php

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Locale\Lang;
use App\Logs;
use App\Response;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SettingsController extends Controller
{
    public function setLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string']);

        $oldPassword = $request->has('oldPassword') ? $request['oldPassword'] : '';
        $oldUsername = $request->has('oldUsername') ? $request['oldUsername'] : '';

        if (Configs::get(false)['password'] === '' && Configs::get(false)['username'] === '' ) {
            Configs::set('username',bcrypt($request['username']));
            Configs::set('password',bcrypt($request['password']));
            return 'true';
        }


        if (Session::has('UserID'))
        {
            $id = Session::get('UserID');
            if ($id == 0)
            {
                if (Configs::get(false)['password'] === '' || Hash::check($oldPassword, Configs::get(false)['password'])) {
                    Configs::set('username',bcrypt($request['username']));
                    Configs::set('password',bcrypt($request['password']));
                    return 'true';
                }

                return Response::error('Current password entered incorrectly!');
            }

            // this is probably sensitive to timing attacks...
            $user = User::find($id);

            if ($user == null)
            {
                Logs::error( __METHOD__, __LINE__, 'User (' . $id . ') does not exist!');
                return Response::error('Could not find User.');
            }

            if ($user->username == $oldUsername && Hash::check($oldPassword, $user->password))
            {
                Logs::notice( __METHOD__, __LINE__, $user->username . ' changed his identity for '.$request['username'].' from ' . $request->ip());
                $user->username = $request['username'];
                $user->password = bcrypt($request['password']);
                $user->save();
                return 'true';
            }
            else
            {
                Logs::notice( __METHOD__, __LINE__, $user->username . ' tried to change his identity from ' . $request->ip());
                return Response::error('Old username or password entered incorrectly!');
            }

        }
    }

    public function setSorting(Request $request)
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

    public function setLang(Request $request) {

        $request->validate([
            'lang'  => 'required|string'
        ]);

        $lang_available = Lang::get_lang_available();
        for ($i = 0; $i < count($lang_available); $i++)
        {
            if($request['lang'] == $lang_available[$i])
            {
                return (Configs::set('lang', $request['lang'])) ? 'true' : 'false';
            }
        }

        Logs::error( __METHOD__, __LINE__, 'Could not update settings. Unknown lang.');
        return 'false';
    }
}
