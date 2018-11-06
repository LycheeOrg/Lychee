<?php

namespace App\Http\Middleware;


use App\Album;
use App\Logs;
use App\Photo;
use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UploadCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // not logged!
        if (!Session::get('login'))
            return response('false');

        $id = Session::get('UserID');
        $user = User::find($id);

        // is admin
        if ($id == 0)
            return $next($request);

        // is not admin and does not have upload rights
        if (!$user->upload)
            return response('false');

        // if albumsID(s) ?
        $ret = $this->album_check($request,$id);

        if ($ret === true)
            return $next($request);
        if ($ret === false)
            return response('false');

        // if photoID(s) ?
        $ret = $this->photo_check($request,$id);
        if ($ret === true)
            return $next($request);
        if ($ret === false)
            return response('false');

        $ret = $this->share_check($request,$id);
        if ($ret === true)
            return $next($request);
        if ($ret === false)
            return response('false');

        // there is nothing about albumID, albumIDs, photoID, photoIDs
        // we have the upload right so it is probably all right.
        return $next($request);

    }

    /**
     * Take of checking if a user can actually modify that Album
     *
     * @param $request
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function album_check(Request $request, int $id)
    {
        if ($request->has('albumID')) {
            $albumID = $request['albumID'];
            if ($albumID == 'f' || $albumID == 's' || $albumID == 'r' || $albumID == 0)
                return true;

            $num = Album::where('id','=',$albumID)->where('owner_id','=',$id)->count();
            if ($num == 0) {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
                return false;
            }
            return true;
        }

        if ($request->has('albumIDs'))
        {
            $albumIDs = $request['albumIDs'];

            $albums = Album::whereIn('id',explode(',', $albumIDs))->get();
            if ($albums == null)
            {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
                return false;
            }
            $no_error = true;
            foreach ($albums as $album_t) {
                $no_error &= ($album_t->owner_id == $id);
            }
            if($no_error)
                return true;

            Logs::error(__METHOD__, __LINE__, 'Album ownership mismatch!');
            return false;
        }

        return null;
    }


    /**
     * Check if the user is authorized to do anything to that picture
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function photo_check(Request $request, int $id)
    {
        if ($request->has('photoID')) {
            $photoID = $request['photoID'];
            $num = Photo::where('id','=',$photoID)->where('owner_id','=',$id)->count();
            if ($num == 0) {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
                return response('false');
            }
            return true;
        }

        if ($request->has('photoIDs'))
        {
            $photoIDs = $request['photoIDs'];

            $photos = Photo::whereIn('id',explode(',', $photoIDs))->get();
            if ($photos == null)
            {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
                return false;
            }
            $no_error = true;
            foreach ($photos as $photo_t) {
                // either you own the picture or it is in an album you own
                $no_error &= (($photo_t->owner_id == $id) || ($photo_t->album != null && $photo_t->album->owner_id == $id));
            }
            if($no_error)
                return true;

            Logs::error(__METHOD__, __LINE__, 'Photos ownership mismatch!');
            return false;
        }
    }

    public function share_check(Request $request, int $id)
    {
        if($request->has('ShareIDs'))
        {
            $shareIDs = $request['ShareIDs'];

            $albums = Album::whereIn('id', function ($query) use ($shareIDs)
                                                {
                                                    $query->select('album_id')
                                                        ->from('user_album')
                                                        ->whereIn('id',explode(',', $shareIDs));
                                                })->select('owner_id')->get();

            if ($albums == null)
            {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified albums');
                return false;
            }
            $no_error = true;
            foreach ($albums as $album_t) {
                $no_error &= ($album_t->owner_id == $id);
            }
            if($no_error)
                return true;

            Logs::error(__METHOD__, __LINE__, 'Album ownership mismatch!');
            return false;

        }
    }
}