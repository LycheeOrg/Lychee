<?php

namespace App\Models;

use App\Assets\HasManyBidirectionally;
use App\Casts\MustNotSetCast;
use App\Facades\AccessControl;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\UTCBasedTimes;
use App\Observers\SizeVariantObserver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local;

// TODO: Uncomment the following line, if Lychee really starts to support AWS s3.
// The previous code already contained some first steps for S3, but relied
// on the fact that the associated disk was called "s3".
// This only requires a string comparison, but is not robust, because the
// disk name can be anything (depending on what the user configures in
// config.php), but does not say anything about the actually used
// driver/adapter.
// Moreover, if any user had ever tried to actually use S3, the code would
// have crashed, because the Laravel framework would try to load the adapter
// below, but the adapter does not exist and is not part of our Composer
// dependencies
//use League\Flysystem\AwsS3v3\AwsS3Adapter;

/**
 * Class SizeVariant.
 *
 * Describes a size variant of a photo.
 *
 * @property int id
 * @property int photo_id
 * @property Photo photo
 * @property int size_variant
 * @property string short_path
 * @property string url
 * @property string full_path
 * @property int width
 * @property int height
 * @property Collection sym_links
 */
class SizeVariant extends Model
{
	use UTCBasedTimes;
	use HasAttributesPatch;
	use HasBidirectionalRelationships;

	const ORIGINAL = 0;
	const MEDIUM2X = 1;
	const MEDIUM = 2;
	const SMALL2X = 3;
	const SMALL = 4;
	const THUMB2X = 5;
	const THUMB = 6;

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
		$this->registerObserver(SizeVariantObserver::class);
	}

	/**
	 * This model has no own timestamps as it is inseparably bound to its
	 * parent {@link \App\Models\Photo} and uses the same timestamps.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * List of those object relations whose timestamps shall be updated when
	 * an object of this class is modified.
	 *
	 * @var string[] list of object relations
	 */
	protected $touches = ['photo'];

	protected $casts = [
		'full_path' => MustNotSetCast::class . ':short_path',
		'url' => MustNotSetCast::class . ':short_path',
		'width' => 'integer',
		'height' => 'integer',
		'size_variant' => 'integer',
	];

	/**
	 * @var string[] The list of attributes which exist as columns of the DB
	 *               relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'id', // irrelevant, because a size variant is always serialized as an embedded object of its photo
		'photo', // see above and otherwise infinite loops will occur
		'photo_id', // see above
		'short_path',  // serialize url instead
		'sym_links', // don't serialize relation of symlinks
	];

	/**
	 * @var string[] The list of "virtual" attributes which do not exist as
	 *               columns of the DB relation but which shall be appended to
	 *               JSON from accessors
	 */
	protected $appends = [
		'url',
	];

	/**
	 * Returns the association to the photo which this size variant belongs
	 * to.
	 *
	 * @return BelongsTo
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class);
	}

	/**
	 * Returns the association to the symbolics links which point to this
	 * size variant.
	 *
	 * @return HasManyBidirectionally
	 */
	public function sym_links(): HasManyBidirectionally
	{
		return $this->hasManyBidirectionally(SymLink::class);
	}

	/**
	 * Accessor for the "virtual" attribute {@link SizeVariant::$url}.
	 *
	 * This is more than a simple convenient method which wraps
	 * {@link SizeVariant::$short_path} into
	 * {@link \Illuminate\Support\Facades\Storage::url()}.
	 * Based on the current application settings and the authenticated user,
	 * this method returns a URL to a short-living symbolic link instead of a
	 * direct URL to the actual size variant, if the underlying storage
	 * provides symbolic links.
	 *
	 * @return string the url of the size variant
	 */
	public function getUrlAttribute(): string
	{
		if (
			(AccessControl::is_admin() && Configs::get_value('SL_for_admin', '0') === '0') ||
			Configs::get_value('SL_enable', '0') == '0'
		) {
			return Storage::url($this->short_path);
		}

		// In order to allow a grace period, we create a new symbolic link,
		// if the most recent existing link has reached 2/3 of its lifetime
		$maxLifetime = intval(Configs::get_value('SL_life_time_days', '3')) * 24 * 60 * 60;
		$gracePeriod = $maxLifetime / 3;

		$storageAdapter = Storage::disk()->getDriver()->getAdapter();

		// TODO: Uncomment these line when Laravel really starts to support s3
		/*if ($storageAdapter instanceof AwsS3Adapter) {
			return Storage::temporaryUrl($this->short_path, now()->addSeconds($maxLifetime));
		}*/

		if ($storageAdapter instanceof Local) {
			/** @var ?SymLink $symLink */
			$symLink = $this->sym_links()->latest()->first();
			if ($symLink == null || $symLink->created_at->isBefore(now()->subSeconds($gracePeriod))) {
				/** @var SymLink $symLink */
				$symLink = $this->sym_links()->create();
			}

			return $symLink->url;
		}

		throw new \InvalidArgumentException('the chosen storage adapter "' . Storage::getDefaultDriver() . '" does not support the symbolic linking feature');
	}

	/**
	 * Accessor for the "virtual" attribute {@link SizeVariant::$full_path}.
	 *
	 * Returns the full path of the size variant as it needs to be input into
	 * some low-level PHP functions like `unlink`.
	 * This is a convenient method and wraps {@link SizeVariant::$short_path}
	 * into {@link \Illuminate\Support\Facades\Storage::path()}.
	 *
	 * @return string the full path of the file
	 */
	public function getFullPathAttribute(): string
	{
		return Storage::path($this->short_path);
	}

	/**
	 * Mutator of the attribute {@link SizeVariant::$size_variant}.
	 *
	 * @param int $sizeVariant the size variant; allowed values are
	 *                         {@link SizeVariant::ORIGINAL},
	 *                         {@link SizeVariant::MEDIUM2X},
	 *                         {@link SizeVariant::MEDIUM},
	 *                         {@link SizeVariant::SMALL2X},
	 *                         {@link SizeVariant::SMALL},
	 *                         {@link SizeVariant::THUMB2X},
	 *                         {@link SizeVariant::THUMB}
	 *
	 * @throws \InvalidArgumentException thrown if `$sizeVariant` is
	 *                                   out-of-bounds
	 */
	public function setSizeVariantAttribute(int $sizeVariant): void
	{
		if (self::ORIGINAL > $sizeVariant || $sizeVariant > self::THUMB) {
			throw new \InvalidArgumentException('passed size variant ' . $sizeVariant . ' out-of-range');
		}
		$this->attributes['size_variant'] = $sizeVariant;
	}

	/**
	 * Deletes this model.
	 *
	 * @param bool $keepFile If true, the associated file is not removed from storage
	 *
	 * @return bool True on success, false otherwise
	 */
	public function delete(bool $keepFile = false): bool
	{
		// Delete all symbolic links pointing to this size variant
		// The observer for the SymLink model takes care of actually erasing
		// the physical symbolic links from disk
		// We must not use a "mass deletion" like $this->sym_links()->delete()
		// here, because this doesn't fire the model events and thus the
		// observer would not delete any actual symbolic link from disk.
		$symLinks = $this->sym_links;
		/** @var SymLink $symLink */
		foreach ($symLinks as $symLink) {
			if ($symLink->delete() === false) {
				return false;
			}
		}

		if ($keepFile) {
			// If short_path is the empty string, SizeVariantObserver does
			// not erase file from disk during the erasing event
			$this->attributes['short_path'] = '';
		}

		return parent::delete();
	}
}
