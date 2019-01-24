<?php

namespace App;

use App\ModelFunctions\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class Photo extends Model
{

	protected $dates = ['created_at', 'updated_at', 'takestamp'];

    public function album()
    {
        return $this->belongsTo('App\Album','album_id','id')->withDefault(['public' => '1']);
    }

	public function owner() {
		return $this->belongsTo('App\User','owner_id','id')->withDefault(['id' => 0, 'username' => 'Admin']);
	}

    /**
     * Returns photo-attributes into a front-end friendly format. Note that some attributes remain unchanged.
     * @return array Returns photo-attributes in a normalized structure.
     */
    public function prepareData() {

        // Excepts the following:
        // (array) $data = ['id', 'title', 'tags', 'public', 'star', 'album', 'thumbUrl', 'takestamp', 'url', 'medium']

        // Init
        $photo = array();

        // Set unchanged attributes
        $photo['id']            = $this->id;
        $photo['title']         = $this->title;
        $photo['tags']          = $this->tags;
        $photo['star']          = $this->star == 1 ? '1' : '0';
        $photo['album']         = $this->album_id;
        $photo['width']         = $this->width;
        $photo['height']        = $this->height;
        $photo['type']          = $this->type;
        $photo['size']          = $this->size;
        $photo['iso']           = $this->iso;
        $photo['aperture']      = $this->aperture;
        $photo['make']          = $this->make;
        $photo['model']         = $this->model;
        $photo['shutter']       = $this->shutter;
        $photo['focal']         = $this->focal;
        $photo['lens']          = $this->lens;
        $photo['latitude']      = $this->latitude;
        $photo['longitude']     = $this->longitude;
        $photo['altitude']      = $this->altitude;
        $photo['sysdate']       = $this->created_at->format('d F Y');
        $photo['tags']          = $this->tags;
        $photo['description']   = $this->description == null ? '' : $this->description;
	    $photo['license']       = Configs::get_value('default_license'); // default

	    if($photo['shutter'] != '' && substr($photo['shutter'], 0,2) != '1/'){


	    	// this should fix it... hopefully.
		    preg_match('/(\d+)\/(\d+) s/', $photo['shutter'], $matches);
		    $a = intval($matches[1]);
		    $b = intval($matches[2]);
		    $gcd = Helpers::gcd($a,$b);
		    $a = $a / $gcd;
		    $b = $b / $gcd;
		    if ($a == 1)
		    {
			    $photo['shutter'] = '1/'. $b . ' s';
		    }
		    else
	        {
		        $photo['shutter'] = ($a / $b) . ' s';
	        }

	    }

	    if ($photo['shutter'] == '1/1 s')
	    {
		    $photo['shutter'] = '1 s';
	    }


		// check if license is none
        if ($this->license == 'none') {

        	// check if it has an album
        	if($this->album_id != 0)
	        {
	        	// this does not include sub albums setting. Do we want this ?
		        // this will need to be changed if we want to add license backtracking
		        $l = $this->album->license;
		        if($l != 'none')
		        {
		        	$photo['license'] = $l;
		        }
	        }
        }
        else {
	        $photo['license'] = $this->license;
        }

        // Parse medium
        if ($this->medium == '1') $photo['medium'] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_MEDIUM') . $this->url;
        else                       $photo['medium'] = '';

	    if ($this->small == '1') $photo['small'] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_SMALL') . $this->url;
	    else                       $photo['small'] = '';

        // Parse paths
        $photo['thumbUrl'] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB') . $this->thumbUrl;
        $photo['url']      = Config::get('defines.urls.LYCHEE_URL_UPLOADS_BIG') . $this->url;

        // Use takestamp as sysdate when possible
        if (isset($this->takestamp) && $this->takestamp != null) {

            // Use takestamp
            $photo['cameraDate'] = '1';
	        $photo['sysdate']    = $this->created_at->format('d F Y');
            $photo['takedate']   = $this->takestamp->format('d F Y \a\t H:i');

        } else {

            // Use sysstamp from the id
            $photo['cameraDate'] = '0';
            $photo['sysdate']    = $this->created_at->format('d F Y');
	        $photo['takedate']   = '';

        }

        $photo['public'] = $this->public == 1 ? '1' : '0';

        if($this->album_id != null)
        {
            $photo['public'] = $this->album->public == '1' ? '2' : $photo['public'];
        }

        return $photo;

    }




    public function predelete(){

        if (Photo::exists($this->checksum, $this->id))
            // it is a duplicate, we do not delete!
            return true;


        $error = false;
        // Delete big
        if (file_exists(Config::get('defines.dirs.LYCHEE_UPLOADS_BIG') . $this->url)&&!unlink(Config::get('defines.dirs.LYCHEE_UPLOADS_BIG') . $this->url)) {
            Logs::error(__METHOD__, __LINE__, 'Could not delete photo in uploads/big/');
            $error = true;
        }

        // Delete medium
        if (file_exists(Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM') . $this->url)&&!unlink(Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM') . $this->url)) {
            Logs::error(__METHOD__, __LINE__, 'Could not delete photo in uploads/medium/');
            $error = true;
        }

	    // Delete medium
	    if (file_exists(Config::get('defines.dirs.LYCHEE_UPLOADS_SMALL') . $this->url)&&!unlink(Config::get('defines.dirs.LYCHEE_UPLOADS_SMALL') . $this->url)) {
		    Logs::error(__METHOD__, __LINE__, 'Could not delete photo in uploads/small/');
		    $error = true;
	    }

	    if($this->thumbUrl!='')
        {
            // Get retina thumb url
            $thumbUrl2x = explode(".", $this->thumbUrl);
            $thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
            // Delete thumb
            if (file_exists(Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB') . $this->thumbUrl)&&!unlink(Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB') . $this->thumbUrl)) {
                Logs::error(__METHOD__, __LINE__, 'Could not delete photo in uploads/thumb/');
                $error = true;
            }

            // Delete thumb@2x
            if (file_exists(Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB') . $thumbUrl2x)&&!unlink(Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB') . $thumbUrl2x)) {
                Logs::error(__METHOD__, __LINE__, 'Could not delete high-res photo in uploads/thumb/');
                $error = true;
            }
        }


        return !$error;

    }


    /*
     *  Defines a bunch of helpers
     */
    /**
     * @param $query
     * @return mixed
     */
    static public function set_order($query){
        return $query->orderBy(Configs::get_value('sortingPhotos_col'),Configs::get_value('sortingPhotos_order'))
            ->orderBy('photos.id','ASC');
    }

    static public function select_stars($query) {
        return self::set_order($query->where('star', '=', 1));
    }

    static public function select_public($query) {
        return self::set_order($query->where('public', '=', 1));
    }

    static public function select_recent($query) {
        return self::set_order($query->where('created_at', '>=', Carbon::now()->subDays(1)->toDateTimeString()));
    }

    static public function select_unsorted($query) {
        return self::set_order($query->where('album_id', '=', null));
    }

    // defines scopes
    public function scopeStars($query)
    {
        return self::select_stars($query);
    }

    public function scopePublic($query)
    {
        return self::select_public($query);
    }

    public function scopeRecent($query)
    {
        return self::select_recent($query);
    }

    public function scopeUnsorted($query)
    {
        return self::select_unsorted($query);
    }

    public function scopeOwnedBy($query,$id)
    {
        return $id == 0 ? $query : $query->where('id','=',$id);
    }

}
