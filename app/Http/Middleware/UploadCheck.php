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
        $this->album_check($request,$next,$id);

        // if photoID(s) ?
        $this->photo_check($request,$next,$id);

        return response('Error: There is a problem with the request');

    }

    /**
     * Take of checking if a user can actually modify that Album
     *
     * @param $request
     * @param Closure $next
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function album_check(Request $request, Closure $next, int $id)
    {
        if ($request->has('albumID')) {
            $albumID = $request['albumID'];
            if ($albumID == 'f' || $albumID == 's' || $albumID == 'r' || $albumID == 0)
                return $next($request);

            $num = Album::where('id','=',$albumID)->where('owner_id','=',$id)->count();
            if ($num == 0) {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
                return response('false');
            }
            return $next($request);
        }

        if ($request->has('albumIDs'))
        {
            $albumIDs = $request['albumIDs'];

            $albums = Album::whereIn('id',$albumIDs)->get();
            if ($albums == null)
            {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
                return response('false');
            }
            $no_error = true;
            foreach ($albums as $album_t) {
                $no_error &= ($album_t->owner_id == $id);
            }
            if($no_error)
                return $next($request);

            Logs::error(__METHOD__, __LINE__, 'Album ownership mismatch!');
            return response('false');
        }
    }


    /**
     * Check if the user is authorized to do anything to that picture
     *
     * @param Request $request
     * @param Closure $next
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function photo_check(Request $request, Closure $next, int $id)
    {
        if ($request->has('photoID')) {
            $photoID = $request['photoID'];
            $num = Photo::where('id','=',$photoID)->where('owner_id','=',$id)->count();
            if ($num == 0) {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
                return response('false');
            }
            return $next($request);
        }

        if ($request->has('photoIDs'))
        {
            $photoIDs = $request['photoIDs'];

            $photos = Photo::whereIn('id',$photoIDs )->get();
            if ($photos == null)
            {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
                return response('false');
            }
            $no_error = true;
            foreach ($photos as $photo_t) {
                // either you own the picture or it is in an album you own
                $no_error &= (($photo_t->owner_id == $id) || ($photo_t->album != null && $photo_t->album->owner_id == $id));
            }
            if($no_error)
                return $next($request);

            Logs::error(__METHOD__, __LINE__, 'Photos ownership mismatch!');
            return response('false');
        }
    }
}