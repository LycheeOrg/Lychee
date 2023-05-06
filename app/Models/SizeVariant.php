<?php

namespace App\Models;

use App\Actions\SizeVariant\Delete;
use App\Casts\MustNotSetCast;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Image\Files\FlysystemFile;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use App\Relations\HasManyBidirectionally;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use League\Flysystem\Local\LocalFilesystemAdapter;

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
// use League\Flysystem\AwsS3v3\AwsS3Adapter;

/**
 * Class SizeVariant.
 *
 * Describes a size variant of a photo.
 *
 * @property int                 $id
 * @property string              $photo_id
 * @property Photo               $photo
 * @property SizeVariantType     $type
 * @property string              $short_path
 * @property string              $url
 * @property string              $full_path
 * @property int                 $width
 * @property int                 $height
 * @property int                 $filesize
 * @property Collection<SymLink> $sym_links
 */
class SizeVariant extends Model
{
	use UTCBasedTimes;
	use HasAttributesPatch;
	use HasBidirectionalRelationships;
	use ThrowsConsistentExceptions;
	/** @phpstan-use UseFixedQueryBuilder<SizeVariant> */
	use UseFixedQueryBuilder;
	use ToArrayThrowsNotImplemented;

	/**
	 * This model has no own timestamps as it is inseparably bound to its
	 * parent {@link \App\Models\Photo} and uses the same timestamps.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * @var array<string,string>
	 */
	protected $casts = [
		'id' => 'integer',
		'type' => SizeVariantType::class,
		'full_path' => MustNotSetCast::class . ':short_path',
		'url' => MustNotSetCast::class . ':short_path',
		'width' => 'integer',
		'height' => 'integer',
		'filesize' => 'integer',
	];

	/**
	 * @var array<int,string> The list of attributes which exist as columns of the DB
	 *                        relation but shall not be serialized to JSON
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

	protected function _toArray(): array
	{
		return parent::toArray();
	}

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
	 *
	 * @throws ConfigurationException
	 */
	public function getUrlAttribute(): string
	{
		$imageDisk = AbstractSizeVariantNamingStrategy::getImageDisk();

		if (
			(Auth::user()?->may_administrate === true && !Configs::getValueAsBool('SL_for_admin')) ||
			!Configs::getValueAsBool('SL_enable')
		) {
			return $imageDisk->url($this->short_path);
		}

		// In order to allow a grace period, we create a new symbolic link,
		// if the most recent existing link has reached 2/3 of its lifetime
		$maxLifetime = Configs::getValueAsInt('SL_life_time_days') * 24 * 60 * 60;
		$gracePeriod = $maxLifetime / 3;

		$storageAdapter = $imageDisk->getAdapter();

		// TODO: Uncomment these line when Laravel really starts to support s3
		/*if ($storageAdapter instanceof AwsS3Adapter) {
			return $imageDisk->temporaryUrl($this->short_path, now()->addSeconds($maxLifetime));
		}*/

		if ($storageAdapter instanceof LocalFilesystemAdapter) {
			/** @var ?SymLink $symLink */
			$symLink = $this->sym_links()->latest()->first();
			if ($symLink === null || $symLink->created_at->isBefore(now()->subSeconds($gracePeriod))) {
				/** @var SymLink $symLink */
				$symLink = $this->sym_links()->create();
			}

			return $symLink->url;
		}

		throw new ConfigurationException('the chosen storage adapter "' . get_class($storageAdapter) . '" does not support the symbolic linking feature');
	}

	/**
	 * Accessor for the "virtual" attribute {@link SizeVariant::$full_path}.
	 *
	 * Returns the full path of the size variant as it needs to be input into
	 * some low-level PHP functions like `unlink`.
	 * This is a convenient method and wraps {@link SizeVariant::$short_path}
	 * into {@link \Illuminate\Support\Facades\Storage::path()}.
	 *
	 * TODO: Remove this method eventually, we must not use paths.
	 *
	 * @return string the full path of the file
	 */
	public function getFullPathAttribute(): string
	{
		return AbstractSizeVariantNamingStrategy::getImageDisk()->path($this->short_path);
	}

	public function getFile(): FlysystemFile
	{
		return new FlysystemFile(AbstractSizeVariantNamingStrategy::getImageDisk(), $this->short_path);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 */
	protected function performDeleteOnModel(): void
	{
		$fileDeleter = (new Delete())->do([$this->id]);
		$this->exists = false;
		$fileDeleter->do();
	}
}
