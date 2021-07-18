<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class RefactorPhotoModel extends Migration
{
	const THUMBNAIL_DIM = 200;
	const THUMBNAIL2X_DIM = 400;

	const VARIANT_ORIGINAL = 0;
	const VARIANT_MEDIUM2X = 1;
	const VARIANT_MEDIUM = 2;
	const VARIANT_SMALL2X = 3;
	const VARIANT_SMALL = 4;
	const VARIANT_THUMB2X = 5;
	const VARIANT_THUMB = 6;

	/**
	 * Maps a size variant to the path prefix (directory) where the file for that size variant is stored.
	 * Use this array to avoid the anti-pattern "magic constants" throughout the whole code.
	 */
	const VARIANT_2_PATH_PREFIX = [
		'big',
		'medium',
		'medium',
		'small',
		'small',
		'thumb',
		'thumb',
	];

	const VALID_VIDEO_TYPES = [
		'video/mp4',
		'video/mpeg',
		'image/x-tga', // mpg; will be corrected by the metadata extractor
		'video/ogg',
		'video/webm',
		'video/quicktime',
		'video/x-ms-asf', // wmv file
		'video/x-ms-wmv', // wmv file
		'video/x-msvideo', // Avi
		'video/x-m4v', // Avi
		'application/octet-stream', // Some mp4 files; will be corrected by the metadata extractor
	];

	const VARIANT_2_WIDTH_ATTRIBUTE = [
		'width',
		'medium2x_width',
		'medium_width',
		'small2x_width',
		'small_width',
	];

	const VARIANT_2_HEIGHT_ATTRIBUTE = [
		'height',
		'medium2x_height',
		'medium_height',
		'small2x_height',
		'small_height',
	];

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->upgradeInconsistentAttributes();
		// We are brutal here and simply drop the old table, as the symlinks
		// will be re-created on-the-fly when required
		Schema::dropIfExists('sym_links');
		Schema::dropIfExists('size_variants');
		$this->createSizeVariantsTable();
		$this->createNewSymLinksTable();
		$this->upgradeMigration();
		$this->sanitizePhotoTable();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$this->downgradeInconsistentAttributes();
		$this->restorePhotoTable();
		$this->downgradeMigration();
		// We are brutal here and simply drop the old table, as the symlinks
		// will be re-created on-the-fly when required
		Schema::dropIfExists('sym_links');
		Schema::dropIfExists('size_variants');
		$this->createOldSymLinksTable();
	}

	/**
	 * Removes some inconsistencies about attributes of the photo table.
	 *
	 *  - At the DB level only snake case should be used, because
	 *    Laravel/Eloquent relies on this assumption.
	 *  - Allows columns to be nullable if the value is unknown/unset.
	 */
	protected function upgradeInconsistentAttributes(): void
	{
		// We have to use raw DB queries here, because Laravel/Eloquent does
		// strange and inconsistent things if we use camel case with
		// high-level API calls
		$dbConnType = Config::get('database.default');
		if ($dbConnType === 'mysql') {
			DB::statement('ALTER TABLE photos RENAME COLUMN `livePhotoUrl` TO live_photo_short_path');
			DB::statement('ALTER TABLE photos RENAME COLUMN `livePhotoContentID` TO live_photo_content_id');
			DB::statement('ALTER TABLE photos RENAME COLUMN `livePhotoChecksum` TO live_photo_checksum');
		} else {
			DB::statement('ALTER TABLE photos RENAME COLUMN "livePhotoUrl" TO live_photo_short_path');
			DB::statement('ALTER TABLE photos RENAME COLUMN "livePhotoContentID" TO live_photo_content_id');
			DB::statement('ALTER TABLE photos RENAME COLUMN "livePhotoChecksum" TO live_photo_checksum');
		}

		Schema::table('photos', function (Blueprint $table) {
			$table->string('tags')->default('')->change();
			$table->string('iso')->nullable(true)->change();
			$table->string('aperture')->nullable(true)->change();
			$table->string('make')->nullable(true)->change();
			$table->string('model')->nullable(true)->change();
			$table->string('lens')->nullable(true)->change();
			$table->string('shutter')->nullable(true)->change();
			$table->string('focal')->nullable(true)->change();
		});
	}

	/**
	 * Reverts {@link RefactorPhotoModel::upgradeInconsistentAttributes()}.
	 */
	protected function downgradeInconsistentAttributes(): void
	{
		$dbConnType = Config::get('database.default');
		if ($dbConnType === 'mysql') {
			DB::statement('ALTER TABLE photos RENAME COLUMN live_photo_short_path TO `livePhotoUrl`');
			DB::statement('ALTER TABLE photos RENAME COLUMN live_photo_content_id TO `livePhotoContentID`');
			DB::statement('ALTER TABLE photos RENAME COLUMN live_photo_checksum TO `livePhotoChecksum`');
		} else {
			DB::statement('ALTER TABLE photos RENAME COLUMN live_photo_short_path TO "livePhotoUrl"');
			DB::statement('ALTER TABLE photos RENAME COLUMN live_photo_content_id TO "livePhotoContentID"');
			DB::statement('ALTER TABLE photos RENAME COLUMN live_photo_checksum TO "livePhotoChecksum"');
		}
	}

	/**
	 * Creates the new table size_variants.
	 *
	 * The table has does not possess own columns for timestamping
	 * (`created_at` and `updated_at`) as the table is tightly coupled to its
	 * parent table `photos` and uses the same timestamps.
	 */
	protected function createSizeVariantsTable(): void
	{
		Schema::create('size_variants', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->foreignId('photo_id')->nullable(false)->constrained();
			$table->unsignedInteger('size_variant')->nullable(false)->default(0)->comment('0: original, ..., 6: thumb');
			$table->string('short_path')->nullable(false);
			$table->integer('width')->nullable(false);
			$table->integer('height')->nullable(false);
			$table->unique(['photo_id', 'size_variant']);
			// Sic! Columns `created_at` and `updated_at` left out by intention
			// the size variants belong to their "parent" photo model and are tied to the same timestamps
		});
	}

	/**
	 * Creates the new table `sym_links`.
	 */
	protected function createNewSymLinksTable(): void
	{
		Schema::create('sym_links', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->foreignId('size_variant_id')->nullable(false)->constrained();
			$table->string('short_path')->nullable(false);
		});
	}

	/**
	 * Re-creates the old table `sym_links`.
	 *
	 * This reverts {@link RefactorPhotoModel::createNewSymLinksTable()}.
	 * The code is mostly copied from {@link CreateSymLinksTable::up()} with
	 * the slight modification that the timestamps are explicitly created
	 * with type `datetime` to ensure consistency across different DB backends.
	 */
	protected function createOldSymLinksTable(): void
	{
		Schema::create('sym_links', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->bigInteger('photo_id')->nullable();
			$table->string('url')->default('');
			$table->string('medium')->default('');
			$table->string('medium2x')->default('');
			$table->string('small')->default('');
			$table->string('small2x')->default('');
			$table->string('thumbUrl')->default('');
			$table->string('thumb2x')->default('');
		});
	}

	/**
	 * Removes obsolete columns from the photo table which are not required
	 * after upgrade.
	 */
	protected function sanitizePhotoTable(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn([
				'url',
				'thumbUrl',
				'thumb2x',
				'width',
				'height',
				'small_width',
				'small_height',
				'small2x_width',
				'small2x_height',
				'medium_width',
				'medium_height',
				'medium2x_width',
				'medium2x_height',
			]);
		});
	}

	/**
	 * Restores columns of the photo table which have been removed by the
	 * upgrade.
	 *
	 * Reverts {@link RefactorPhotoModel::sanitizePhotoTable()}.
	 */
	protected function restorePhotoTable(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->string('url', 100)->default('');
			$table->string('thumbUrl', 37)->default('');
			$table->boolean('thumb2x')->default(false);
			for ($i = self::VARIANT_ORIGINAL; $i <= self::VARIANT_SMALL; $i++) {
				$table->integer(self::VARIANT_2_WIDTH_ATTRIBUTE[$i])->unsigned()->nullable()->default(null);
				$table->integer(self::VARIANT_2_HEIGHT_ATTRIBUTE[$i])->unsigned()->nullable()->default(null);
			}
		});
	}

	/**
	 * Fills the table size_variants based on the values of the table photos.
	 *
	 * The actual work horse of this migration.
	 */
	protected function upgradeMigration(): void
	{
		DB::beginTransaction();

		$photos = DB::table('photos')->select(array_merge([
			'id',
			'url',
			'type',
			'thumbUrl',
			'thumb2x',
		], self::VARIANT_2_WIDTH_ATTRIBUTE, self::VARIANT_2_HEIGHT_ATTRIBUTE
		))->lazyById();

		foreach ($photos as $photo) {
			for ($variant = self::VARIANT_ORIGINAL; $variant <= self::VARIANT_THUMB; $variant++) {
				if ($this->hasSizeVariant($photo, $variant)) {
					DB::table('size_variants')->insert([
						'photo_id' => $photo->id,
						'size_variant' => $variant,
						'short_path' => $this->getShortPathOfPhoto($photo, $variant),
						'width' => $this->getWidth($photo, $variant),
						'height' => $this->getHeight($photo, $variant),
					]);
				}
			}
		}

		DB::commit();
	}

	/**
	 * Fills the table photos based on the values of the table size_variants.
	 *
	 * Reverts {@link RefactorPhotoModel::upgradeMigration()}.
	 */
	protected function downgradeMigration(): void
	{
		DB::beginTransaction();

		$photos = DB::table('photos')->select([
			'id',
			'type',
			'checksum',
		])->lazyById();

		foreach ($photos as $photo) {
			$update = [];
			$sizeVariants = DB::table('size_variants')
				->where('photo_id', '=', $photo->id)
				->get();
			/** @var object $sizeVariant */
			foreach ($sizeVariants as $sizeVariant) {
				$fileExtension = '.' . pathinfo($sizeVariant->short_path, PATHINFO_EXTENSION);
				$expectedBasename = substr($photo->checksum, 0, 32);
				if (
					$sizeVariant->size_variant == self::VARIANT_THUMB2X ||
					$sizeVariant->size_variant == self::VARIANT_SMALL2X ||
					$sizeVariant->size_variant == self::VARIANT_MEDIUM2X
				) {
					$expectedBasename .= '@2x';
				}
				$expectedFilename = $expectedBasename . $fileExtension;
				$expectedPathPrefix = self::VARIANT_2_PATH_PREFIX[$sizeVariant->size_variant] . '/';
				if ($sizeVariant->size_variant == self::VARIANT_ORIGINAL && $this->isRaw($photo)) {
					$expectedPathPrefix = 'raw/';
				}
				$expectedShortPath = $expectedPathPrefix . $expectedFilename;

				// Ensure that the size variant is stored at the location which
				// is expected acc. to the old naming scheme
				if ($sizeVariant->short_path != $expectedShortPath) {
					Storage::move($sizeVariant->short_path, $expectedShortPath);
				}

				if ($sizeVariant->size_variant == self::VARIANT_THUMB2X) {
					$update['thumb2x'] = true;
				} elseif ($sizeVariant->size_variant == self::VARIANT_THUMB) {
					$update['thumbUrl'] = $expectedFilename;
				} else {
					if ($sizeVariant->size_variant == self::VARIANT_ORIGINAL) {
						$update['url'] = $expectedFilename;
					}
					$update[self::VARIANT_2_WIDTH_ATTRIBUTE[$sizeVariant->size_variant]] = $sizeVariant->width;
					$update[self::VARIANT_2_HEIGHT_ATTRIBUTE[$sizeVariant->size_variant]] = $sizeVariant->width;
				}
			}

			DB::table('photos')
				->where('id', '=', $photo->id)
				->update($update);
		}
		DB::commit();
	}

	/**
	 * Returns the short path of a picture file for the designated size
	 * variant from an old-style photo wrt. to the old naming scheme.
	 *
	 * @param object $photo an object with attributes of the old photo table
	 *
	 * @return string the short path
	 */
	public function getShortPathOfPhoto(object $photo, int $variant): string
	{
		$origFilename = $photo->url;
		$thumbFilename = $photo->thumbUrl;
		$thumbFilename2x = $this->add2xToFilename($thumbFilename);
		$otherFilename = ($this->isVideo($photo) || $this->isRaw($photo)) ? $thumbFilename : $origFilename;
		$otherFilename2x = $this->add2xToFilename($otherFilename);
		switch ($variant) {
			case self::VARIANT_THUMB:
				$filename = $thumbFilename;
				break;
			case self::VARIANT_THUMB2X:
				$filename = $thumbFilename2x;
				break;
			case self::VARIANT_SMALL:
			case self::VARIANT_MEDIUM:
				$filename = $otherFilename;
				break;
			case self::VARIANT_SMALL2X:
			case self::VARIANT_MEDIUM2X:
				$filename = $otherFilename2x;
				break;
			case self::VARIANT_ORIGINAL:
				$filename = $origFilename;
				break;
			default:
				throw new InvalidArgumentException('Invalid size variant: ' . $variant);
		}
		$directory = self::VARIANT_2_PATH_PREFIX[$variant] . '/';
		if ($variant === self::VARIANT_ORIGINAL && $this->isRaw($photo)) {
			$directory = 'raw/';
		}

		return $directory . $filename;
	}

	protected function isVideo(object $photo): bool
	{
		return in_array($photo->type, self::VALID_VIDEO_TYPES, true);
	}

	protected function isRaw(object $photo): bool
	{
		return $photo->type == 'raw';
	}

	/**
	 * Given a filename generates the @2x corresponding filename.
	 * This is used for thumbs, small and medium.
	 */
	protected function add2xToFilename(string $filename): string
	{
		$filename2x = explode('.', $filename);

		return (count($filename2x) === 2) ?
			$filename2x[0] . '@2x.' . $filename2x[1] :
			$filename2x[0] . '@2x';
	}

	protected function getWidth(object $photo, int $variant): int
	{
		switch ($variant) {
			case self::VARIANT_THUMB:
				return self::THUMBNAIL_DIM;
			case self::VARIANT_THUMB2X:
				return self::THUMBNAIL2X_DIM;
			case self::VARIANT_SMALL:
				return $photo->small_width ?: 0;
			case self::VARIANT_SMALL2X:
				return $photo->small2x_width ?: 0;
			case self::VARIANT_MEDIUM:
				return $photo->medium_width ?: 0;
			case self::VARIANT_MEDIUM2X:
				return $photo->medium2x_width ?: 0;
			case self::VARIANT_ORIGINAL:
				return $photo->width;
			default:
				throw new InvalidArgumentException('Invalid size variant: ' . $variant);
		}
	}

	protected function getHeight(object $photo, int $variant): int
	{
		switch ($variant) {
			case self::VARIANT_THUMB:
				return self::THUMBNAIL_DIM;
			case self::VARIANT_THUMB2X:
				return self::THUMBNAIL2X_DIM;
			case self::VARIANT_SMALL:
				return $photo->small_height ?: 0;
			case self::VARIANT_SMALL2X:
				return $photo->small2x_height ?: 0;
			case self::VARIANT_MEDIUM:
				return $photo->medium_height ?: 0;
			case self::VARIANT_MEDIUM2X:
				return $photo->medium2x_height ?: 0;
			case self::VARIANT_ORIGINAL:
				return $photo->height;
			default:
				throw new InvalidArgumentException('Invalid size variant: ' . $variant);
		}
	}

	protected function hasSizeVariant(object $photo, int $variant): bool
	{
		if ($variant == self::VARIANT_ORIGINAL || $variant == self::VARIANT_THUMB) {
			return true;
		} elseif ($variant == self::VARIANT_THUMB2X) {
			return (bool) ($photo->thumb2x);
		} else {
			return $this->getWidth($photo, $variant) != 0;
		}
	}
}
