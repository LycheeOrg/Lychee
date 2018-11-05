<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Imagick;
use ImagickException;
use ImagickPixel;

class Photo extends Model
{
    public function album()
    {
        return $this->belongsTo('App\Album','album_id','id');
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
        $photo['id']     = $this->id;
        $photo['title']  = $this->title;
        $photo['tags']   = $this->tags;
        $photo['star']   = $this->star == 1 ? '1' : '0';
        $photo['album']  = $this->album_id;
        $photo['width']  = $this->width;
        $photo['height'] = $this->height;
        $photo['type']         = $this->type;
        $photo['size']         = $this->size;
        $photo['iso']         = $this->iso;
        $photo['aperture']    = $this->aperture;
        $photo['make']        = $this->make;
        $photo['model']       = $this->model;
        $photo['shutter']     = $this->shutter;
        $photo['focal']       = $this->focal;
//        $photo['takestamp']   = 0;
//        $photo['lens']        = $this->lens;
        $photo['sysdate']    = $this->created_at->format('d F Y');
        $photo['tags']        = $this->tags;
        $photo['description']  = $this->description == null ? '' : $this->description;

        // Parse medium
        if ($this->medium == '1') $photo['medium'] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_MEDIUM') . $this->url;
        else                       $photo['medium'] = '';

        // Parse paths
        $photo['thumbUrl'] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB') . $this->thumbUrl;
        $photo['url']      = Config::get('defines.urls.LYCHEE_URL_UPLOADS_BIG') . $this->url;

        // Use takestamp as sysdate when possible
        if (isset($this->takestamp) && $this->takestamp !=='0') {

            // Use takestamp
            $photo['cameraDate'] = '1';
            $photo['takedate']    = strftime('%d %B %Y at %H:%M', $this->takestamp);

        } else {

            // Use sysstamp from the id
            $photo['cameraDate'] = '0';
            $photo['takedate']    = $this->created_at->format('d F Y');

        }

        $photo['public'] = $this->public == 1? '1' : '0';

        if($this->album_id != null)
        {
            $photo['public'] = $this->album->public == '1' ? '2' : $photo['public'];
        }

        return $photo;

    }


    /**
     * @param string $checksum
     * @param $photoID
     * @return array|false Returns a subset of a photo when same photo exists or returns false on failure.
     */
    static public function exists(string $checksum, $photoID = null) {

        $sql = Photo::where('checksum','=',$checksum);
        if (isset($photoID))
            $sql = $sql->where('id', '<>', $photoID);

        return ($sql->count() == 0) ? false : $sql->first();
    }


    /**
     * Reads and parses information and metadata out of a photo.
     * @param string $url
     * @return array Returns an array of photo information and metadata.
     */
    static public function getInformations(string $url) {
//        Logs::notice(__METHOD__, __LINE__, 'Get Info: '.$url);

        // Functions returns information and metadata of a photo
        // Excepts the following:
        // (string) $url = Path to photo-file
        // Returns the following:
        // (array) $return

        $iptcArray = array();
        $info      = getimagesize($url, $iptcArray);
        // General information
        $return['type']        = $info['mime'];
        $return['width']       = $info[0];
        $return['height']      = $info[1];
        $return['title']       = '';
        $return['description'] = '';
        $return['orientation'] = '';
        $return['iso']         = '';
        $return['aperture']    = '';
        $return['make']        = '';
        $return['model']       = '';
        $return['shutter']     = '';
        $return['focal']       = '';
        $return['takestamp']   = 0;
        $return['lens']        = '';
        $return['tags']        = '';
        $return['position']    = '';
        $return['latitude']    = '';
        $return['longitude']   = '';
        $return['altitude']    = '';

        // Size
        $size = filesize($url)/1024;
        if ($size>=1024) $return['size'] = round($size/1024, 1) . ' MB';
        else $return['size'] = round($size, 1) . ' KB';

        // IPTC Metadata
        // See https://www.iptc.org/std/IIM/4.2/specification/IIMV4.2.pdf for mapping
        if(isset($iptcArray['APP13'])) {

            $iptcInfo = iptcparse($iptcArray['APP13']);
            if (is_array($iptcInfo)) {

                // Title
                if (!empty($iptcInfo['2#105'][0])) $return['title'] = $iptcInfo['2#105'][0];
                else if (!empty($iptcInfo['2#005'][0])) $return['title'] = $iptcInfo['2#005'][0];

                // Description
                if (!empty($iptcInfo['2#120'][0])) $return['description'] = $iptcInfo['2#120'][0];

                // Tags
                if (!empty($iptcInfo['2#025'])) $return['tags'] = implode(',', $iptcInfo['2#025']);

                // Position
                $fields = array();
                if (!empty($iptcInfo['2#090'])) $fields[] = trim($iptcInfo['2#090'][0]);
                if (!empty($iptcInfo['2#092'])) $fields[] = trim($iptcInfo['2#092'][0]);
                if (!empty($iptcInfo['2#095'])) $fields[] = trim($iptcInfo['2#095'][0]);
                if (!empty($iptcInfo['2#101'])) $fields[] = trim($iptcInfo['2#101'][0]);

                if (!empty($fields)) $return['position'] = implode(', ', $fields);

            }

        }

        // Read EXIF
        if ($info['mime']=='image/jpeg') $exif = @exif_read_data($url, 'EXIF', false, false);
        else $exif = false;

        // EXIF Metadata
        if ($exif!==false) {

            // Orientation
            if (isset($exif['Orientation'])) $return['orientation'] = $exif['Orientation'];
            else if (isset($exif['IFD0']['Orientation'])) $return['orientation'] = $exif['IFD0']['Orientation'];

            // ISO
            if (!empty($exif['ISOSpeedRatings'])) $return['iso'] = $exif['ISOSpeedRatings'];

            // Aperture
            if (!empty($exif['COMPUTED']['ApertureFNumber'])) $return['aperture'] = $exif['COMPUTED']['ApertureFNumber'];

            // Make
            if (!empty($exif['Make'])) $return['make'] = trim($exif['Make']);

            // Model
            if (!empty($exif['Model'])) $return['model'] = trim($exif['Model']);

            // Exposure
            if (!empty($exif['ExposureTime'])) $return['shutter'] = $exif['ExposureTime'] . ' s';

            // Focal Length
            if (!empty($exif['FocalLength'])) {
                if (strpos($exif['FocalLength'], '/')!==false) {
                    $temp = explode('/', $exif['FocalLength'], 2);
                    $temp = $temp[0] / $temp[1];
                    $temp = round($temp, 1);
                    $return['focal'] = $temp . ' mm';
                } else {
                    $return['focal'] = $exif['FocalLength'] . ' mm';
                }
            }

            // Takestamp
            if (!empty($exif['DateTimeOriginal'])) $return['takestamp'] = strtotime($exif['DateTimeOriginal']);

            // Lens field from Lightroom
            if (!empty($exif['UndefinedTag:0xA434'])) $return['lens'] = trim($exif['UndefinedTag:0xA434']);

            // Deal with GPS coordinates
            if (!empty($exif['GPSLatitude']) && !empty($exif['GPSLatitudeRef'])) $return['latitude'] = Helpers::getGPSCoordinate($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
            if (!empty($exif['GPSLongitude']) && !empty($exif['GPSLongitudeRef'])) $return['longitude'] = Helpers::getGPSCoordinate($exif['GPSLongitude'], $exif['GPSLongitudeRef']);

        }

        return $return;

    }

    /**
     * Rotates and flips a photo based on its EXIF orientation.
     * @return array|false Returns an array with the new orientation, width, height or false on failure.
     * @throws ImagickException
     */
    static public function adjustFile($path, array $info) {

        // Excepts the following:
        // (string) $path = Path to the photo-file
        // (array) $info = ['orientation', 'width', 'height']

        $swapSize = false;

        if (extension_loaded('imagick')&&Configs::get()['imagick']==='1') {

            $image = new Imagick();
            $image->readImage($path);

            $orientation = $image->getImageOrientation();

            switch ($orientation) {

                case Imagick::ORIENTATION_TOPLEFT: return false; break;
                case Imagick::ORIENTATION_TOPRIGHT: $image->flopImage(); break;
                case Imagick::ORIENTATION_BOTTOMRIGHT: $image->rotateImage(new ImagickPixel(), 180); break;
                case Imagick::ORIENTATION_BOTTOMLEFT: $image->flopImage(); $image->rotateImage(new ImagickPixel(), 180); break;
                case Imagick::ORIENTATION_LEFTTOP: $image->flopImage(); $image->rotateImage(new ImagickPixel(), -90); $swapSize = true; break;
                case Imagick::ORIENTATION_RIGHTTOP: $image->rotateImage(new ImagickPixel(), 90); $swapSize = true; break;
                case Imagick::ORIENTATION_RIGHTBOTTOM: $image->flopImage(); $image->rotateImage(new ImagickPixel(), 90); $swapSize = true; break;
                case Imagick::ORIENTATION_LEFTBOTTOM: $image->rotateImage(new ImagickPixel(), -90); $swapSize = true; break;
                default: return false; break;

            }

            // Adjust photo
            $image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
            $image->writeImage($path);

            // Free memory
            $image->clear();
            $image->destroy();

        } else {

            $newWidth  = $info['width'];
            $newHeight = $info['height'];
            $sourceImg = imagecreatefromjpeg($path);

            switch ($info['orientation']) {

                // do nothing
                case 1: return false; break;

                // mirror
                case 2: imageflip($sourceImg, IMG_FLIP_HORIZONTAL); break;

                case 3: $sourceImg = imagerotate($sourceImg, -180, 0); break;

                // rotate 180 and mirror
                case 4: imageflip($sourceImg, IMG_FLIP_VERTICAL); break;

                // rotate 90 and mirror
                case 5:
                    $sourceImg = imagerotate($sourceImg, -90, 0);
                    $newWidth  = $info['height'];
                    $newHeight = $info['width'];
                    $swapSize  = true;
                    imageflip($sourceImg, IMG_FLIP_HORIZONTAL);
                    break;

                case 6:
                    $sourceImg = imagerotate($sourceImg, -90, 0);
                    $newWidth  = $info['height'];
                    $newHeight = $info['width'];
                    $swapSize  = true;
                    break;

                // rotate -90 and mirror
                case 7:
                    $sourceImg = imagerotate($sourceImg, 90, 0);
                    $newWidth  = $info['height'];
                    $newHeight = $info['width'];
                    $swapSize  = true;
                    imageflip($sourceImg, IMG_FLIP_HORIZONTAL);
                    break;

                case 8:
                    $sourceImg = imagerotate($sourceImg, 90, 0);
                    $newWidth  = $info['height'];
                    $newHeight = $info['width'];
                    $swapSize  = true;
                    break;

                default:
                    return false;
                    break;

            }

            // Recreate photo
            // In this step the photos also loses its metadata :(
            $newSourceImg = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newSourceImg, $sourceImg, 0, 0, 0, 0, $newWidth, $newHeight, $newWidth, $newHeight);
            imagejpeg($newSourceImg, $path, 100);

            // Free memory
            imagedestroy($sourceImg);
            imagedestroy($newSourceImg);

        }

        // SwapSize should be true when the image has been rotated
        // Return new dimensions in this case
        if ($swapSize===true) {
            $swapSize       = $info['width'];
            $info['width']  = $info['height'];
            $info['height'] = $swapSize;
        }

        return $info;

    }

    /**
     * @return boolean Returns true when successful.
     * @throws \ImagickException
     */
    function createThumb() {

        $filename = $this->url;
        $type = $this->type;
        $width = $this->width;
        $height = $this->height;
        $url = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$filename;
        // Quality of thumbnails
        $quality = 90;

        // Size of the thumbnail
        $newWidth  = 200;
        $newHeight = 200;

        $photoName = explode('.', $filename);
        $newUrl    = Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB'). $photoName[0] . '.jpeg';
        $newUrl2x  = Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB'). $photoName[0] . '@2x.jpeg';

        // Create thumbnails with Imagick
        if(Configs::hasImagick()) {

            // Read image
            $thumb = new Imagick();
            $thumb->readImage($url);
            $thumb->setImageCompressionQuality($quality);
            $thumb->setImageFormat('jpeg');

            // Remove metadata to save some bytes
            $thumb->stripImage();

            // Copy image for 2nd thumb version
            $thumb2x = clone $thumb;

            // Create 1st version
            $thumb->cropThumbnailImage($newWidth, $newHeight);
            $thumb->writeImage($newUrl);
            $thumb->clear();
            $thumb->destroy();

            // Create 2nd version
            $thumb2x->cropThumbnailImage($newWidth*2, $newHeight*2);
            $thumb2x->writeImage($newUrl2x);
            $thumb2x->clear();
            $thumb2x->destroy();

        } else {

            // Create image
            $thumb   = imagecreatetruecolor($newWidth, $newHeight);
            $thumb2x = imagecreatetruecolor($newWidth*2, $newHeight*2);

            // Set position
            if ($width<$height) {
                $newSize     = $width;
                $startWidth  = 0;
                $startHeight = $height/2 - $width/2;
            } else {
                $newSize     = $height;
                $startWidth  = $width/2 - $height/2;
                $startHeight = 0;
            }

            // Create new image
            switch($type) {
                case 'image/jpeg': $sourceImg = imagecreatefromjpeg($url); break;
                case 'image/png':  $sourceImg = imagecreatefrompng($url); break;
                case 'image/gif':  $sourceImg = imagecreatefromgif($url); break;
                default:           Logs::error(__METHOD__, __LINE__, 'Type of photo is not supported');
                    return false;
                    break;
            }

            // Create thumb
            Helpers::fastImageCopyResampled($thumb, $sourceImg, 0, 0, $startWidth, $startHeight, $newWidth, $newHeight, $newSize, $newSize);
            imagejpeg($thumb, $newUrl, $quality);
            imagedestroy($thumb);

            // Create retina thumb
            Helpers::fastImageCopyResampled($thumb2x, $sourceImg, 0, 0, $startWidth, $startHeight, $newWidth*2, $newHeight*2, $newSize, $newSize);
            imagejpeg($thumb2x, $newUrl2x, $quality);
            imagedestroy($thumb2x);

            // Free memory
            imagedestroy($sourceImg);

        }

        return true;

    }

    /**
     * Creates a smaller version of a photo when its size is bigger than a preset size.
     * Photo must be big enough and Imagick must be installed and activated.
     * @return boolean Returns true when successful.
     * @throws \ImagickException
     */
    function createMedium() {

        // Excepts the following:
        // (string) $url = Path to the photo-file
        // (string) $filename = Name of the photo-file
        // (int) $width = Width of the photo
        // (int) $height = Height of the photo

        $filename = $this->url;
        $width = $this->width;
        $height = $this->height;

        $url = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$filename;

        // Quality of medium-photo
        $quality = 90;

        // Set to true when creation of medium-photo failed
        $error = false;

        // Size of the medium-photo
        // When changing these values,
        // also change the size detection in the front-end
        $newWidth  = 1920;
        $newHeight = 1080;

        // Check permissions
        if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM'))===false) {

            // Permissions are missing
            Logs::notice(__METHOD__, __LINE__, 'Skipped creation of medium-photo, because uploads/medium/ is missing or not readable and writable.');
            $error = true;

        }

        // Is photo big enough?
        // Is Imagick installed and activated?
        if (($error===false)&&
            ($width>$newWidth||$height>$newHeight)&&
            (Configs::hasImagick())) {
            Logs::notice(__METHOD__, __LINE__, 'Picture is big enough for resize!');
            $newUrl = Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM') . $filename;

            // Read image
            $medium = new Imagick();
            $medium->readImage($url);

            // Adjust image
            $medium->scaleImage($newWidth, $newHeight, true);
            $medium->stripImage();
            $medium->setImageCompressionQuality($quality);

            // Save image
            try { $medium->writeImage($newUrl); }
            catch (ImagickException $err) {
                Logs::notice(__METHOD__, __LINE__, 'Could not save medium-photo (' . $err->getMessage() . ')');
                $error = true;
            }

            $medium->clear();
            $medium->destroy();

        } else {

            // Photo too small or
            // Medium is deactivated or
            // Imagick not installed
            Logs::notice(__METHOD__, __LINE__, 'No resize!');
            $error = true;

        }

        if ($error===true) return false;
        return true;

    }

    public function predelete(){

        if (Photo::exists($this->checksum, $this->id))
            // it is a duplicate, we do not delete!
            return true;

        // Get retina thumb url
        $thumbUrl2x = explode(".", $this->thumbUrl);
        $thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];

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
        return $id == 0 ? $query : $query->where('owner_id','=',$id);
    }
}
