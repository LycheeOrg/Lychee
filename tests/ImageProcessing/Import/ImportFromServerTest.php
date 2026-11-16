<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\ImageProcessing\Import;

use App\Enum\UserUploadTrustLevel;
use App\Jobs\ImportImageJob;
use App\Jobs\RecomputeAlbumSizeJob;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\CreatesImportTree;

class ImportFromServerTest extends BaseApiWithDataTest
{
	use CreatesImportTree;

	public function setUp(): void
	{
		parent::setUp();
		$this->createImportTree();
		$this->admin = $this->grantFullAccess($this->admin);
	}

	public function tearDown(): void
	{
		$this->removeImportTree();
		parent::tearDown();
	}

	private function getDefaultPayload(string $directory, ?string $album_id = null): array
	{
		return [
			'directories' => [$directory],
			'delete_imported' => false,
			'import_via_symlink' => false,
			'skip_duplicates' => false,
			'resync_metadata' => false,
			'delete_missing_photos' => false,
			'delete_missing_albums' => false,
			'album_id' => $album_id,
		];
	}

	public function testImportFromServertAsGuest(): void
	{
		$response = $this->postJson('/Import', $this->getDefaultPayload('tests'));
		$this->assertUnauthorized($response);
	}

	public function testImportFromServertLoggedIn(): void
	{
		$response = $this->actingAs($this->userLocked)->postJson('/Import', $this->getDefaultPayload('tests'));
		$this->assertForbidden($response);
	}

	public function testImportFromServertWrongDirNotAuthorized(): void
	{
		$payload = $this->getDefaultPayload('wrong-dir');
		$response = $this->actingAs($this->userLocked)->postJson('/Import', $payload);
		$this->assertForbidden($response);
	}

	public function testImportFromServertConflictingOptions(): void
	{
		$payload = $this->getDefaultPayload('tests');
		$payload['delete_imported'] = true;
		$payload['import_via_symlink'] = true;
		$response = $this->actingAs($this->userLocked)->postJson('/Import', $payload);
		$this->assertUnprocessable($response);
	}

	public function testImportFromServertWrongDir(): void
	{
		$payload = $this->getDefaultPayload('wrong-dir');
		$response = $this->actingAs($this->admin)->postJson('/Import', $payload);
		$this->assertUnprocessable($response);
	}

	public function testImportFromServerSuccess(): void
	{
		// No need to dispatch. :)
		Queue::fake();

		$directory = 'tests/Samples/sync';

		$response = $this->actingAs($this->admin)->postJson('/Import', $this->getDefaultPayload($directory, $this->album5->id));
		$this->assertCreated($response);
		$response->assertJsonStructure([
			'status',
			'message',
			'results' => [
				['directory', 'status', 'jobs_count'],
			],
			'job_count',
		]);
		$response->assertJson(['status' => true]);

		// Check that jobs were created
		$this->assertGreaterThan(0, $response->json('job_count'));
		// Creating the album already queued its RecomputeAlbumSizeJob and RecomputeAlbumStatsJob:
		// the import's own copies (counted in job_count) are dropped while those wait.
		Queue::assertCount($response->json('job_count'));
		Queue::assertPushed(ImportImageJob::class, $response->json('job_count') - 2);
		Queue::assertPushedOn('low', RecomputeAlbumSizeJob::class);
		Queue::assertPushedOn('low', RecomputeAlbumStatsJob::class);
	}

	/** S-115-09: a restricted user imports inside their root as themselves. */
	public function testRestrictedImportInsideOwnedByUser(): void
	{
		Queue::fake();
		$alice = $this->restrictTo($this->userMayUpload1, $this->import_root);

		$response = $this->actingAs($alice)->postJson('/Import', $this->getDefaultPayload($this->import_root . '/2026', $this->album1->id));
		$this->assertCreated($response);

		$album = Album::query()->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('base_albums.title', '=', '2026')->where('albums.parent_id', '=', $this->album1->id)->first();
		self::assertNotNull($album);
		self::assertSame($alice->id, $album->owner_id);
		Queue::assertPushed(ImportImageJob::class, fn (ImportImageJob $job) => $job->intended_owner_id === $alice->id);
		Queue::assertNotPushed(ImportImageJob::class, fn (ImportImageJob $job) => $job->intended_owner_id !== $alice->id);
	}

	/** S-115-09: an admin with full access imports as themselves, not as the `owner_id` user. */
	public function testAdminImportOwnedByAdmin(): void
	{
		Queue::fake();
		$response = $this->actingAs($this->admin)->postJson('/Import', $this->getDefaultPayload($this->import_root . '/2026', null));
		$this->assertCreated($response);
		Queue::assertPushed(ImportImageJob::class, fn (ImportImageJob $job) => $job->intended_owner_id === $this->admin->id);
	}

	/** S-115-10: one directory outside → nothing imported. */
	public function testRestrictedImportWithOutsideDirectoryForbidden(): void
	{
		Queue::fake();
		$alice = $this->restrictTo($this->userMayUpload1, $this->import_root);
		$payload = $this->getDefaultPayload($this->import_root . '/2026', $this->album1->id);
		$payload['directories'][] = $this->import_base . '/outside';

		$response = $this->actingAs($alice)->postJson('/Import', $payload);
		$this->assertForbidden($response);
		Queue::assertNothingPushed();
		self::assertSame(0, BaseAlbumImpl::query()->where('title', '=', '2026')->count());
	}

	/** S-115-10: a link outside is refused like an outside path. */
	public function testRestrictedImportThroughOutsideLinkForbidden(): void
	{
		Queue::fake();
		$alice = $this->restrictTo($this->userMayUpload1, $this->import_root);
		$response = $this->actingAs($alice)->postJson('/Import', $this->getDefaultPayload($this->import_root . '/link-outside', $this->album1->id));
		$this->assertForbidden($response);
	}

	/** S-115-11 */
	public function testImportIntoAlbumWithoutUploadRightForbidden(): void
	{
		Queue::fake();
		$alice = $this->restrictTo($this->userMayUpload1, $this->import_root);
		$response = $this->actingAs($alice)->postJson('/Import', $this->getDefaultPayload($this->import_root . '/2026', $this->album2->id));
		$this->assertForbidden($response);
	}

	/** S-115-28 */
	public function testRestrictedSymlinkImportRefused(): void
	{
		Queue::fake();
		$alice = $this->restrictTo($this->userMayUpload1, $this->import_root);
		$payload = $this->getDefaultPayload($this->import_root . '/2026', $this->album1->id);
		$payload['import_via_symlink'] = true;

		$response = $this->actingAs($alice)->postJson('/Import', $payload);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors('import_via_symlink');
	}

	/** Feature 115 (S-115-12, web): a `check` user's imported photos need validation. */
	public function testWebImportTrustLevelFollowsRequester(): void
	{
		$alice = $this->restrictTo($this->userMayUpload1, $this->import_root);
		$alice->upload_trust_level = UserUploadTrustLevel::CHECK;
		$alice->save();
		Queue::fake();

		$response = $this->actingAs($alice->fresh())->postJson('/Import', $this->getDefaultPayload($this->import_root . '/2026', $this->album1->id));
		$this->assertCreated($response);
		Queue::assertPushed(ImportImageJob::class, fn (ImportImageJob $job) => $job->upload_trust_level === UserUploadTrustLevel::CHECK && $job->import_root === $this->import_root);
	}
}
