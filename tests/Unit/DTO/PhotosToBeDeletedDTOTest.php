<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\DTO;

use App\Constants\QueryLimits;
use App\DTO\Delete\PhotosToBeDeletedDTO;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Jobs\FileDeleterJob;
use App\Jobs\RecomputePersonStatsJob;
use App\Models\Album;
use App\Models\Face;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Person;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;
use Tests\Traits\CreatesBulkFixtures;

/**
 * FR-112-14: a force delete handles each chunk of photos from start to end,
 * so the rows of a chunk are read once.
 *
 * Fixture: 502 photos (two chunks). The first one has every size variant,
 * the others one ORIGINAL each. Three of them are changed:
 * - first (chunk 1): watermarked ORIGINAL, live photo on the local disk;
 * - second (chunk 1): ORIGINAL sold, live photo on the local disk;
 * - last (chunk 2): ORIGINAL on S3, watermarked, live photo on S3.
 * Faces: one with a person on the first photo, one dismissed with another
 * person and one without a person on the last photo.
 */
class PhotosToBeDeletedDTOTest extends AbstractTestCase
{
	use DatabaseTransactions;
	use CreatesBulkFixtures;

	/** Tables holding rows of a photo, read or written per chunk. */
	private const PHOTO_ROW_TABLES = ['size_variants', 'photos', 'faces', 'statistics', 'palettes', 'nsfw_detections', 'photo_moderation_events', 'photo_album'];

	/** @var string[] */
	private array $photo_ids = [];

	private int $sold_variant_id = 0;

	private string $counted_person_id = '';

	/** @var string[] */
	private array $face_ids = [];

	/** @var array<string,string[]> disk => paths the FileDeleterJobs must carry */
	private array $expected_paths = [];

	public function setUp(): void
	{
		parent::setUp();
		$this->createFixture();
	}

	/**
	 * S-112-24: every unsold variant path, every watermarked path and every
	 * live-photo path is queued on its disk; the sold ORIGINAL keeps its row
	 * and file; no job holds more than 500 paths.
	 *
	 * @return void
	 */
	public function testForceDeleteQueuesThePathsOfEveryDiskAndKind(): void
	{
		$jobs = $this->dto()->executeDelete();

		$queued = [];
		foreach ($jobs as $job) {
			if (!$job instanceof FileDeleterJob) {
				continue;
			}
			self::assertLessThanOrEqual(FileDeleterJob::BATCH_SIZE, count($job->file_list));
			$queued[$job->storage_type->value] = array_merge($queued[$job->storage_type->value] ?? [], $job->file_list);
		}
		foreach (StorageDiskType::cases() as $disk) {
			self::assertEqualsCanonicalizing($this->expected_paths[$disk->value] ?? [], $queued[$disk->value] ?? [], 'Paths on ' . $disk->value);
		}

		$this->assertDatabaseHas('size_variants', ['id' => $this->sold_variant_id, 'photo_id' => null]);
		self::assertSame(0, DB::table('photos')->whereIn('id', $this->photo_ids)->count());
		self::assertSame(0, DB::table('size_variants')->whereIn('photo_id', $this->photo_ids)->count());
	}

	/**
	 * S-112-24, faces: every face id goes to the embedding job, and only the
	 * persons of faces that are not dismissed are recounted, across chunks.
	 *
	 * @return void
	 */
	public function testForceDeleteCollectsTheFacesOfEveryChunk(): void
	{
		$jobs = collect($this->dto()->executeDelete());

		$person_jobs = $jobs->filter(fn ($job) => $job instanceof RecomputePersonStatsJob)->values();
		self::assertCount(1, $person_jobs);
		self::assertSame([$this->counted_person_id], array_values($person_jobs[0]->person_ids));

		$embedding_jobs = $jobs->filter(fn ($job) => $job instanceof DeleteFaceEmbeddingsJob)->values();
		self::assertCount(1, $embedding_jobs);
		self::assertEqualsCanonicalizing($this->face_ids, $embedding_jobs[0]->face_ids);
	}

	/**
	 * S-112-25: one SELECT on size_variants and one on faces per chunk, and
	 * every statement on the photo rows of the first chunk runs before any
	 * on the second.
	 *
	 * @return void
	 */
	public function testForceDeleteReadsEachChunkOnceAndInOrder(): void
	{
		$chunks = array_chunk($this->photo_ids, QueryLimits::ID_CHUNK_SIZE);
		self::assertCount(2, $chunks);

		/** @var QueryExecuted[] $queries */
		$queries = [];
		DB::listen(function (QueryExecuted $query) use (&$queries): void {
			$queries[] = $query;
		});

		$this->dto()->executeDelete();

		$selects = array_filter($queries, fn (QueryExecuted $q) => str_starts_with(strtolower(ltrim($q->sql)), 'select'));
		self::assertCount(2, array_filter($selects, fn (QueryExecuted $q) => self::mentions($q->sql, 'size_variants')), 'SELECTs reading size_variants');
		self::assertCount(2, array_filter($selects, fn (QueryExecuted $q) => self::mentions($q->sql, 'faces')), 'SELECTs reading faces');

		$last_of_first = -1;
		$first_of_second = PHP_INT_MAX;
		foreach ($queries as $index => $query) {
			if (!self::mentionsAny($query->sql, self::PHOTO_ROW_TABLES)) {
				continue;
			}
			if (array_intersect($query->bindings, $chunks[0]) !== []) {
				$last_of_first = max($last_of_first, $index);
			}
			if (array_intersect($query->bindings, $chunks[1]) !== []) {
				$first_of_second = min($first_of_second, $index);
			}
		}
		self::assertGreaterThanOrEqual(0, $last_of_first, 'No statement on the first chunk');
		self::assertLessThan($first_of_second, $last_of_first, 'A statement on the first chunk runs after one on the second');
	}

	private function dto(): PhotosToBeDeletedDTO
	{
		return new PhotosToBeDeletedDTO(force_delete_photo_ids: $this->photo_ids);
	}

	private function createFixture(): void
	{
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();
		$this->photo_ids = $this->bulkPhotos($owner, QueryLimits::ID_CHUNK_SIZE + 2, [$album->id]);
		$first = $this->photo_ids[0];
		$sold = $this->photo_ids[1];
		$last = $this->photo_ids[count($this->photo_ids) - 1];

		$this->original($first)->update(['short_path_watermarked' => 'wm/first.jpg']);
		DB::table('photos')->where('id', '=', $first)->update(['live_photo_short_path' => 'live/first.mov']);

		$this->original($last)->update(['storage_disk' => StorageDiskType::S3->value, 'short_path_watermarked' => 'wm/last.jpg']);
		DB::table('photos')->where('id', '=', $last)->update(['live_photo_short_path' => 'live/last.mov']);

		$this->sold_variant_id = intval($this->original($sold)->value('id'));
		DB::table('photos')->where('id', '=', $sold)->update(['live_photo_short_path' => 'live/sold.mov']);
		$order = Order::factory()->create(['user_id' => $owner->id]);
		OrderItem::factory()->create([
			'order_id' => $order->id,
			'photo_id' => $sold,
			'size_variant_id' => $this->sold_variant_id,
			'title' => 'sold',
		]);

		$counted = Person::factory()->create();
		$dismissed = Person::factory()->create();
		$first_photo = Photo::query()->findOrFail($first);
		$last_photo = Photo::query()->findOrFail($last);
		$this->counted_person_id = $counted->id;
		$this->face_ids = [
			Face::factory()->for_photo($first_photo)->for_person($counted)->create()->id,
			Face::factory()->for_photo($last_photo)->for_person($dismissed)->dismissed()->create()->id,
			Face::factory()->for_photo($last_photo)->create()->id,
		];

		// Read before the delete: every unsold variant path and watermarked
		// path on its disk, and the live photos on the disk of their ORIGINAL.
		foreach (DB::table('size_variants')->whereIn('photo_id', $this->photo_ids)->get() as $variant) {
			if (intval($variant->id) === $this->sold_variant_id) {
				continue;
			}
			$this->expected_paths[$variant->storage_disk][] = $variant->short_path;
			if ($variant->short_path_watermarked !== null) {
				$this->expected_paths[$variant->storage_disk][] = $variant->short_path_watermarked;
			}
		}
		$this->expected_paths[StorageDiskType::LOCAL->value][] = 'live/first.mov';
		$this->expected_paths[StorageDiskType::LOCAL->value][] = 'live/sold.mov';
		$this->expected_paths[StorageDiskType::S3->value][] = 'live/last.mov';
	}

	private function original(string $photo_id): Builder
	{
		return DB::table('size_variants')
			->where('photo_id', '=', $photo_id)
			->where('type', '=', SizeVariantType::ORIGINAL->value);
	}

	private static function mentions(string $sql, string $table): bool
	{
		return preg_match('/["`]' . $table . '["`]/', $sql) === 1;
	}

	/**
	 * @param string[] $tables
	 */
	private static function mentionsAny(string $sql, array $tables): bool
	{
		foreach ($tables as $table) {
			if (self::mentions($sql, $table)) {
				return true;
			}
		}

		return false;
	}
}
