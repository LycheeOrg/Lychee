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
        $album['hidden'] = strval($this->visible_hidden);

//        $album['owned'] =

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
     * @param string $password
     * @return boolean Returns when album is public.
     */
    public function checkPassword(string $password) {

        // Check if password is correct
        return ($this->password == '' || Hash::check($password, $this->password));
//        if ($this->password == '') return true;
//        if ($this->password === crypt($password, $this->password)) return true;
//        return false;

    }

    public function update_min_max_takestamp() {
        $min = Photo::where('album_id','=',$this->id)->min('takestamp');
        $max = Photo::where('album_id','=',$this->id)->max('takestamp');
        $this->min_takestamp = min($this->min_takestamp, $min);
        $this->max_takestamp = max($this->max_takestamp, $max);
    }

    static public function reset_takestamp() {
        $albums = Album::all();
        foreach($albums as $album)
        {
            $album->update_min_max_takestamp();
            $album->save();
        }
    }

    public function owner() {
        return $this->belongsTo('App\User','owner_id','id');
    }


    public static function merge($albums1, $albums2) {

    }
}
