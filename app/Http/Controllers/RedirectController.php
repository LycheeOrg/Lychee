<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\ModelFunctions\SessionFunctions;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RedirectController extends Controller
{

        /**
         * @var SessionFunctions
         */
        private $sessionFunctions;

        /**
         * @param SessionFunctions    $sessionFunctions
         */
        public function __construct(
                SessionFunctions $sessionFunctions
        ) {
                $this->sessionFunctions = $sessionFunctions;
        }

        /**
         * Trivial redirection.
         *
         * @param Request $request
         * @param string $albumid
         *
         */
        public function album(Request $request, $albumid)
        {
                if (!$this->sessionFunctions->has_visible_album($albumid) && $request['password'] != '') {
                        $this->unlockPasswordAlbum($request['password']);
                }
                return redirect('gallery#' . $albumid);
        }

        /**
         * Trivial redirection.
         *
         * @param Request $request
         * @param string $albumid
         * @param string $photoid
         */
        public function photo(Request $request, $albumid, $photoid)
        {
                if (!$this->sessionFunctions->has_visible_album($albumid) && $request['password'] != '') {
                        $this->unlockPasswordAlbum($request['password']);
                }
                return redirect('gallery#' . $albumid . '/' . $photoid);
        }

        /**
         * Provided an password, add all the albums that the password unlocks
         *
         * @param string $password
         */
        private function unlockPasswordAlbum(string $password) {
                $albums = Album::whereNotNull('password')->where('password', '!=', '')->get();
                $albumIDs = [];
                foreach ($albums as $album) {
                        if (Hash::check($password, $album->password)) {
                                $albumIDs[] = $album->id;
                        }
                }
                $this->sessionFunctions->add_visible_albums($albumIDs);
        }
}
