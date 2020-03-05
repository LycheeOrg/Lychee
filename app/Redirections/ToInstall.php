<?php

namespace App\Redirections;

class ToInstall implements Redirection {

    static public function go()
    {
        // we remove installed.log in order to be able to access the install menu.
        @unlink(base_path('installed.log'));

        return redirect(route('install-welcome'), 307, [
            'Cache-Control' => 'no-cache, must-revalidate'
            ]);
    }   
}


