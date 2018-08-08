<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Album extends Model
{
    public function photos()
    {
        return $this->hasMany('App\Photo','album_id','id');
    }


    /**
     * Rurns album-attributes into a front-end friendly format. Note that some attributes remain unchanged.
     * @return array Returns album-attributes in a normalized structure.
     */
    public function prepareData() {

        // This function requires the following album-attributes and turns them
        // into a front-end friendly format: id, title, public, sysstamp, password
        // Note that some attributes remain unchanged

        // Init
        $album = array();

        // Set unchanged attributes
        $album['id']     = $this->id;
        $album['title']  = $this->title;
        $album['public'] = strval($this->public);

        // Additional attributes
        // Only part of $album when available
        $album['description'] = strval($this->description);
        $album['visible'] = strval($this->visible_hidden);
        $album['downloadable'] = strval($this->downloadable);

        // Parse date
        $album['sysdate'] = $this->created_at->format('F Y');

        // Parse password
        $album['password'] = ($this->password == '' ? '0' : '1');

        // Parse thumbs or set default value
        $album['thumbs'] = explode(',', $this->thumbs);

        return $album;
    }

    /**
     * @return boolean Returns when album is public.
     */
    public function checkPassword($password) {

        // Check if password is correct
        return ($this->password == '' || Hash::check($password, $this->password));
//        if ($this->password == '') return true;
//        if ($this->password === crypt($password, $this->password)) return true;
//        return false;

    }
}
