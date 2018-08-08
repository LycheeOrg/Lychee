<?php

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Logs;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{

    public function init(Request $request)
    {
        if (Session::get('login') === true && Session::get('identifier')=== Configs::get(false)['identifier'])
        {
            /**
             * Admin Access
             * Full access to Lychee. Only with correct password/session.
             */
            $public = false;

        } else {
            /**
             * Guest Access
             * Access to view all public folders and photos in Lychee.
             */
            $public = true;
        }

//        Logs::notice(__METHOD__,__LINE__,'init Session');

        // Return settings
        $return = array();

        // Path to Lychee for the server-import dialog
//        $return['config']['location'] = Config::get('defines.path.LYCHEE');

        // Check if login credentials exist and login if they don't
        if (self::noLogin() === true || $public === false) {

            // Logged in
            $return['config'] = Configs::get(false);
            $return['config']['login'] = !$public;
            $return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');

        } else {
            // Logged out
            $return['config'] = Configs::get();
            $return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDOUT');
        }

        return Response::json($return);

    }

    public function login(Request $request)
    {
        $request->validate([
            'user' => 'required',
            'password' => 'required'
        ]);

        $configs = Configs::get(false);
        if (Hash::check($request['user'], $configs['username']) && Hash::check($request['password'], $configs['password'])) {
            Session::put('login',true);
            Session::put('identifier',$configs['identifier']);
            Logs::notice( __METHOD__, __LINE__, 'User (' . $request['user'] . ') has logged in from ' . $request->ip());
            return 'true';
        }

        // No login
        if (self::noLogin()===true)
        {
            Logs::warning( __METHOD__, __LINE__, 'DEFAULT LOGIN!');
            return 'true';
        }

        Logs::error(__METHOD__, __LINE__, 'User (' . $request['user'] . ') has tried to log in from ' . $request->ip());

        return 'false';

    }

    /**
     * Sets the session values when no there is no username and password in the database.
     * @return boolean Returns true when no login was found.
     */
    static private function noLogin() {

        $configs = Configs::get(false);
        // Check if login credentials exist and login if they don't
        if (isset($configs['username']) && $configs['username'] ==='' &&
            isset($configs['password']) && $configs['password']==='') {
            Session::put('login',true);
            Session::put('identifier', $configs['identifier']);
            return true;
        }
        unset($configs);
        return false;
    }

    /**
     * Unsets the session values.
     * @return boolean Returns true when logout was successful.
     */
    public function logout() {

        Session::flush();

        return 'true';

    }

    /**
     * Unsets the session values.
     * @return boolean Returns true when logout was successful.
     */
    public function show() {
        dd(Session::all());
    }

    static public function checkAccess($request, $return, $refuse){
        if (Session::get('login'))
        return $return;

        $album = Album::find($request['albumID']);
        if($album == null) return $refuse;
        if($album->public != 1) return $refuse;
        if($album->password == '') return $return;
        if(!Session::has('visible_albums')) return $refuse;
        $visible_albums = Session::get('visible_albums');
        $visible_albums = explode('|',$visible_albums);
        $found = false;
        foreach ($visible_albums as $visible_album)
        {
        $found |= ($visible_album == $request['albumID']);
        }
        if($found) return $return;

        return $refuse;
    }
}
