<?php

namespace App\Redirections;

class ToHome implements Redirection {

    static public function go()
    {
        // we directly redirect to gallery
        return redirect(route('home'), 307, [
            'Cache-Control' => 'no-cache, must-revalidate'
        ]);
    }   
}


