<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Rename columns that remain in photos to proper snake_case
		DB::statement('ALTER TABLE photos RENAME COLUMN "livePhotoUrl" TO live_photo_short_path');
		DB::statement('ALTER TABLE photos RENAME COLUMN "livePhotoContentID" TO live_photo_content_id');
		DB::statement('ALTER TABLE photos RENAME COLUMN "livePhotoChecksum" TO live_photo_checksum');

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

		// We are brutal here and simply drop the old table, as the symlinks
		// will be re-created on-the-fly when required
		Schema::dropIfExists('sym_links');
		Schema::dropIfExists('size_variants');

		// Create table size_variants
		Schema::create('size_variants', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->foreignId('photo_id')->nullable(false)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->unsignedInteger('size_variant')->nullable(false)->default(0)->comment('0: original, ..., 6: thumb');
			$table->string('short_path')->nullable(false);
			$table->integer('width')->nullable(false);
			$table->integer('height')->nullable(false);
			$table->unique(['photo_id', 'size_variant']);
			// Sic! Columns `created_at` and `updated_at` left out by intention
			// the size variants belong to their "parent" photo model and are tied to the same timestamps
		});

		// Re-create table `sym_links`
		Schema::create('sym_links', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->foreignId('size_variant_id')->nullable(false)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->string('short_path')->nullable(false);
		});

		DB::beginTransaction();

		// Copy columns from `photo` to `size_variant`
		$photos = DB::table('photos')->select([
			'id',
			'url',
			'type',
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
		])->lazyById();

		foreach ($photos as $photo) {
			for ($variant = self::VARIANT_ORIGINAL; $variant <= self::VARIANT_THUMB; $variant++) {
				if ($this->hasSizeVariant($variant, $photo)) {
					DB::table('size_variants')->insert([
						'photo_id' => $photo->id,
						'size_variant' => $variant,
						'short_path' => $this->getShortPath($variant, $photo),
						'width' => $this->getWidth($variant, $photo),
						'height' => $this->getHeight($variant, $photo),
					]);
				}
			}
		}

		DB::commit();

		// drop the obsolete columns from photos
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
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Rename columns that remained in photos back to their original name
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('live_photo_filename', 'livePhotoUrl');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('live_photo_content_id', 'livePhotoContentID');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('live_photo_checksum', 'livePhotoChecksum');
		});

		// Re-create the columns in photos that have been removed
		Schema::table('photos', function (Blueprint $table) {
			$table->string('url', 100);
			$table->string('thumbUrl', 37)->default('');
			$table->boolean('thumb2x')->default(false);
			$table->integer('width')->unsigned()->nullable()->default(null);
			$table->integer('height')->unsigned()->nullable()->default(null);
			$table->integer('small_width')->unsigned()->nullable()->default(null);
			$table->integer('small_height')->unsigned()->nullable()->default(null);
			$table->integer('small2x_width')->unsigned()->nullable()->default(null);
			$table->integer('small2x_height')->unsigned()->nullable()->default(null);
			$table->integer('medium_width')->unsigned()->nullable()->default(null);
			$table->integer('medium_height')->unsigned()->nullable()->default(null);
			$table->integer('medium2x_width')->unsigned()->nullable()->default(null);
			$table->integer('medium2x_height')->unsigned()->nullable()->default(null);
		});

		DB::beginTransaction();

		// TODO: Write back migration here. We have a problem, if the new URLs do not follow the old pattern.

		DB::commit();

		// Drop newly created table
		Schema::dropIfExists('size_variants');

		// Re-create table `sym_links` acc. to the old schema
		// We are brutal here and simply drop the old table, as the symlinks
		// will be re-created on-the-fly when required
		Schema::dropIfExists('sym_links');
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

	public function getShortPath(int $variant, object $photo): string
	{
		return self::VARIANT_2_PATH_PREFIX[$variant] . '/' . $this->getFilename($variant, $photo);
	}

	/**
	 * Returns the base filename without any directory or alike.
	 *
	 * @param object $photo an array with columns of the old photo table
	 *
	 * @return string the base filename
	 */
	public function getFilename(int $variant, object $photo): string
	{
		$filename = $photo->url;
		$thumbFilename = $photo->thumbUrl;
		if ($this->isVideo($photo) || $this->isRaw($photo)) {
			$filename = $thumbFilename;
		}
		$filename2x = $this->ex2x($filename);
		$thumbFilename2x = $this->ex2x($thumbFilename);

		switch ($variant) {
			case self::VARIANT_THUMB:
				return $thumbFilename;
			case self::VARIANT_THUMB2X:
				return $thumbFilename2x;
			case self::VARIANT_SMALL:
			case self::VARIANT_MEDIUM:
			case self::VARIANT_ORIGINAL:
				return $filename;
			case self::VARIANT_SMALL2X:
			case self::VARIANT_MEDIUM2X:
				return $filename2x;
			default:
				throw new InvalidArgumentException('Invalid size variant: ' . $variant);
		}
	}

	public function isVideo(object $photo): bool
	{
		return in_array($photo->type, self::VALID_VIDEO_TYPES, true);
	}

	public function isRaw(object $photo): bool
	{
		return $photo->type == 'raw';
	}

	/**
	 * Given a filename generate the @2x corresponding filename.
	 * This is used for thumbs, small and medium.
	 */
	public function ex2x(string $filename): string
	{
		$filename2x = explode('.', $filename);

		return (count($filename2x) === 2) ?
			$filename2x[0] . '@2x.' . $filename2x[1] :
			$filename2x[0] . '@2x';
	}

	public function getWidth(int $variant, object $photo): int
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

	public function getHeight(int $variant, object $photo): int
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

	public function hasSizeVariant(int $variant, object $photo): bool
	{
		if ($variant == self::VARIANT_ORIGINAL || $variant == self::VARIANT_THUMB) {
			return true;
		} elseif ($variant == self::VARIANT_THUMB2X) {
			return (bool) ($photo->thumb2x);
		} else {
			return $this->getWidth($variant, $photo) != 0;
		}
	}
}
