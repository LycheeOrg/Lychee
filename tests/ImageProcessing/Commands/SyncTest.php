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

namespace Tests\ImageProcessing\Commands;

use App\Constants\PhotoAlbum as PA;
use App\Enum\UserUploadTrustLevel;
use App\Jobs\ImportImageJob;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use function Safe\exec;
use function Safe\mkdir;
use function Safe\unlink;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\CreatesImportTree;

class SyncTest extends BaseApiWithDataTest
{
	use CreatesImportTree;

	public const COMMAND = 'lychee:sync';

	public function setUp(): void
	{
		parent::setUp();
		$this->createImportTree();
	}

	public function tearDown(): void
	{
		$this->removeImportTree();
		parent::tearDown();
	}

	// S-024-01 regression guard: conflicting flags still fail
	public function testFailedArgs(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--delete_imported' => '1',
			'-v' => '1',
			'paths' => ['.'],
		])
		->assertFailed()
		->assertExitCode(1);
	}

	// S-024-01: directory sync behaviour unchanged
	public function testSuccess(): void
	{
		Configs::set('skip_duplicates_early', '1');
		Configs::set('sync_delete_missing_photos', '1');
		Configs::set('sync_delete_missing_albums', '1');

		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'paths' => ['./tests/Samples/sync'],
		])
		->assertSuccessful();

		$album = DB::table('base_albums')->select('id')->where('title', 'sync')->first();
		$this->assertNotNull($album, 'Album "sync" should have been created.');

		$photo = DB::table('photos')->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')->where(PA::ALBUM_ID, $album->id)->first();
		$this->assertNotNull($photo, 'Photo "png" should have been created.');
		$this->assertEquals('png', $photo->title, 'Photo title should be "png".');

		// Second run should not create duplicates
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'paths' => ['./tests/Samples/sync'],
		])
		->assertSuccessful();

		$this->assertEquals(1, DB::table('base_albums')->select('id')->where('title', 'sync')->count());
		$this->assertEquals(1, DB::table('photos')->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')->where(PA::ALBUM_ID, $album->id)->count());

		Configs::set('skip_duplicates_early', '0');
		Configs::set('sync_delete_missing_photos', '0');
		Configs::set('sync_delete_missing_albums', '0');
	}

	// S-024-02: single file path with supported extension is imported into the target album
	public function testSyncSingleFile(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => ['./tests/Samples/png.png'],
		])
		->assertSuccessful();

		$photo = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->first();
		$this->assertNotNull($photo, 'Photo should have been imported into the target album.');
		$this->assertEquals('png', $photo->title, 'Photo title should be "png".');
	}

	// S-024-03: unsupported extension is warned and skipped, exit 0
	public function testSyncFileUnsupportedExtension(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => ['./tests/Samples/xcf.xcf'],
		])
		->assertSuccessful();

		$count = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->count();
		$this->assertEquals(0, $count, 'No photos should have been imported for unsupported extension.');
	}

	// S-024-04: non-existent path produces error and exits 1
	public function testSyncNonExistentPath(): void
	{
		$this->artisan(self::COMMAND, [
			'--owner_id' => $this->admin->id,
			'paths' => ['/nonexistent/path/to/file.jpg'],
		])
		->assertFailed()
		->assertExitCode(1);
	}

	// S-024-05 / S-024-08: multiple file paths are all imported
	public function testSyncMultipleFiles(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => [
				'./tests/Samples/png.png',
				'./tests/Samples/gif.gif',
			],
		])
		->assertSuccessful();

		$count = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->count();
		$this->assertEquals(2, $count, 'Both files should have been imported.');
	}

	// S-024-05: mixed directory and file paths are processed together
	public function testSyncMixedPaths(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => [
				'./tests/Samples/sync',
				'./tests/Samples/png.png',
			],
		])
		->assertSuccessful();

		// Directory sync should have created a "sync" sub-album under album5
		$sub_album = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('albums.parent_id', $this->album5->id)
			->where('base_albums.title', 'sync')
			->first();
		$this->assertNotNull($sub_album, 'Sub-album "sync" should have been created from directory sync.');

		// The file should have been imported directly into album5
		$direct_photo = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->first();
		$this->assertNotNull($direct_photo, 'File should have been imported directly into album5.');
	}

	// S-024-07: delete_missing flags are inactive when only file paths are supplied
	public function testSyncFileDeleteMissingNotice(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--delete_missing_photos' => '1',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => ['./tests/Samples/gif.gif'],
		])
		->assertSuccessful();

		// Verify the file was imported (command ran successfully despite delete_missing_photos=1)
		$count = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->count();
		$this->assertEquals(1, $count, 'GIF should have been imported even when delete_missing_photos=1 is set for file mode.');
	}

	/** Feature 115 (S-115-12): photos take the owner's trust level. */
	public function testTrustLevelFollowsOwner(): void
	{
		$owner = User::factory()->may_upload()->create();
		$owner->upload_trust_level = UserUploadTrustLevel::CHECK;
		$owner->save();

		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $owner->id,
			'paths' => [$this->import_root . '/2026'],
		])->assertSuccessful();

		$photo = Photo::query()->where('owner_id', '=', $owner->id)->firstOrFail();
		self::assertSame(UserUploadTrustLevel::CHECK->value, DB::table('photos')->where('id', '=', $photo->id)->value('upload_trust_level'));
		self::assertFalse((bool) DB::table('photos')->where('id', '=', $photo->id)->value('is_validated'));
	}

	/** Feature 115 (S-115-13): admins are always trusted. */
	public function testAdminOwnerIsTrusted(): void
	{
		$this->admin->upload_trust_level = UserUploadTrustLevel::CHECK;
		$this->admin->save();

		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'paths' => [$this->import_root . '/2026'],
		])->assertSuccessful();

		$photo_id = DB::table('photos')->where('owner_id', '=', $this->admin->id)->where('title', '=', 'b')->value('id');
		self::assertNotNull($photo_id);
		self::assertSame(UserUploadTrustLevel::TRUSTED->value, DB::table('photos')->where('id', '=', $photo_id)->value('upload_trust_level'));
		self::assertTrue((bool) DB::table('photos')->where('id', '=', $photo_id)->value('is_validated'));
	}

	/** Feature 115 (S-115-26): an explicit CLI path that is a link (folder or file) is refused. */
	public function testExplicitLinkPathRefused(): void
	{
		Queue::fake();

		foreach ([$this->import_root . '/link-inside/', $this->import_root . '/file-outside.jpg'] as $path) {
			$this->artisan(self::COMMAND, ['--owner_id' => $this->admin->id, 'paths' => [$path]])
				->expectsOutputToContain('Path is a symbolic link')
				->assertExitCode(1);
		}
		Queue::assertNothingPushed();
	}

	/** Feature 115 (S-115-26): an explicit CLI path skips links too. */
	public function testExplicitPathSkipsLinks(): void
	{
		Queue::fake();

		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'paths' => [$this->import_root],
		])->assertSuccessful();

		$files = Queue::pushed(ImportImageJob::class)->map(fn (ImportImageJob $job) => basename($job->file_path))->all();
		sort($files);
		self::assertSame(['a.jpg', 'b.jpg', 'c.jpg'], $files);
		Queue::assertPushed(ImportImageJob::class, fn (ImportImageJob $job) => $job->import_root === null);
	}

	/**
	 * `<base>/Example/{a.jpg, Keep/k.jpg, Keep/Sub/s.jpg}`.
	 */
	private function createExampleTree(): string
	{
		$example = $this->import_base . '/Example';
		mkdir($example . '/Keep/Sub', 0777, true);
		$this->placeImage($example . '/a.jpg', 'tests/Samples/night.jpg');
		$this->placeImage($example . '/Keep/k.jpg', 'tests/Samples/train.jpg');
		$this->placeImage($example . '/Keep/Sub/s.jpg', 'tests/Samples/mongolia.jpeg');

		return $example;
	}

	private function sync(string $path, ?string $album_id, bool $delete_missing = false): void
	{
		$args = [
			'--import_via_symlink' => '0',
			'--delete_imported' => '0',
			'--skip_duplicates' => '0',
			'--owner_id' => $this->admin->id,
			'--delete_missing_photos' => $delete_missing ? '1' : '0',
			'--delete_missing_albums' => $delete_missing ? '1' : '0',
			'--dry_run' => '0',
			'paths' => [$path],
		];
		if ($album_id !== null) {
			$args['--album_id'] = $album_id;
		}
		$this->artisan(self::COMMAND, $args)->assertSuccessful();
	}

	private function albumId(string $title, ?string $parent_id): ?string
	{
		return DB::table('albums')->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('base_albums.title', '=', $title)->where('albums.parent_id', '=', $parent_id)->value('albums.id');
	}

	private function queuedAlbumOf(string $basename): ?string
	{
		return Queue::pushed(ImportImageJob::class)->first(fn (ImportImageJob $job) => basename($job->file_path) === $basename)?->album_id;
	}

	/** Feature 115 (S-115-14): the selected folder becomes an album (default). */
	public function testRootFolderAsAlbum(): void
	{
		Queue::fake();
		$example = $this->createExampleTree();

		$this->sync($example, $this->album5->id);

		$example_id = $this->albumId('Example', $this->album5->id);
		self::assertNotNull($example_id);
		self::assertSame($example_id, $this->queuedAlbumOf('a.jpg'));
		self::assertNotNull($this->albumId('Keep', $example_id));
	}

	/** Feature 115 (S-115-15): the folder's content goes straight into the target album. */
	public function testRootFolderContentIntoTargetAlbum(): void
	{
		Queue::fake();
		Configs::set('import_root_folder_as_album', '0');
		$example = $this->createExampleTree();

		$this->sync($example, $this->album5->id);

		self::assertNull($this->albumId('Example', $this->album5->id));
		$keep_id = $this->albumId('Keep', $this->album5->id);
		self::assertNotNull($keep_id);
		self::assertSame($this->album5->id, $this->queuedAlbumOf('a.jpg'));
		self::assertSame($keep_id, $this->queuedAlbumOf('k.jpg'));
		self::assertSame($this->albumId('Sub', $keep_id), $this->queuedAlbumOf('s.jpg'));
		Configs::set('import_root_folder_as_album', '1');
	}

	/** Feature 115 (S-115-32): at the gallery root, root images go to Unsorted and the root level is never cleaned. */
	public function testRootFolderContentIntoGalleryRoot(): void
	{
		Queue::fake();
		Configs::set('import_root_folder_as_album', '0');
		$example = $this->createExampleTree();

		$this->sync($example, null);

		$keep_id = $this->albumId('Keep', null);
		self::assertNotNull($keep_id);
		self::assertNull($this->albumId('Example', null));
		self::assertNull($this->queuedAlbumOf('a.jpg'), 'a.jpg goes to Unsorted');
		$sub_id = $this->albumId('Sub', $keep_id);
		self::assertNotNull($sub_id);
		$ghost = Photo::factory()->owned_by($this->admin)->with_title('ghost')->in(Album::query()->findOrFail($keep_id))->create();
		$kept = Photo::factory()->owned_by($this->admin)->with_title('k')->in(Album::query()->findOrFail($keep_id))->create();
		$unsorted = Photo::factory()->owned_by($this->admin)->with_title('unsorted-ghost')->create();

		exec('rm -rf ' . escapeshellarg($example . '/Keep/Sub'));
		unlink($example . '/a.jpg');
		$this->sync($example, null, delete_missing: true);

		self::assertNull($this->albumId('Sub', $keep_id), 'Sub is gone from disk');
		self::assertNotNull($this->albumId('Keep', null));
		self::assertSame(5, DB::table('albums')->whereIn('id', [$this->album1->id, $this->album2->id, $this->album3->id, $this->album4->id, $this->album5->id])->count(), 'root-level albums untouched');
		self::assertNull(Photo::query()->find($ghost->id));
		self::assertNotNull(Photo::query()->find($kept->id));
		self::assertNotNull(Photo::query()->find($unsorted->id), 'Unsorted photos are never deleted');
		self::assertNotNull(Photo::query()->find($this->photoUnsorted->id));
		Configs::set('import_root_folder_as_album', '1');
	}

	/** Feature 115 (S-115-33): with a target album, delete-missing cleans the target itself. */
	public function testRootFolderContentDeleteMissingInTargetAlbum(): void
	{
		Queue::fake();
		Configs::set('import_root_folder_as_album', '0');
		$example = $this->createExampleTree();

		$this->sync($example, $this->album5->id);
		$keep_id = $this->albumId('Keep', $this->album5->id);
		self::assertNotNull($keep_id);
		$ghost = Photo::factory()->owned_by($this->admin)->with_title('ghost')->in($this->album5)->create();
		$a = Photo::factory()->owned_by($this->admin)->with_title('a')->in($this->album5)->create();

		exec('rm -rf ' . escapeshellarg($example . '/Keep'));
		$this->sync($example, $this->album5->id, delete_missing: true);

		self::assertNull($this->albumId('Keep', $this->album5->id));
		self::assertNull(Photo::query()->find($ghost->id));
		self::assertNotNull(Photo::query()->find($a->id));
		Configs::set('import_root_folder_as_album', '1');
	}

	/** Feature 115 (S-115-29): an album of another user with the same title is not reused. */
	public function testOwnerScopeDoesNotReuseOthersAlbum(): void
	{
		Queue::fake();
		$example = $this->createExampleTree();
		Album::factory()->as_root()->owned_by($this->userMayUpload2)->with_title('Example')->create();

		$this->sync($example, null);

		$owners = DB::table('albums')->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('base_albums.title', '=', 'Example')->whereNull('albums.parent_id')->pluck('base_albums.owner_id')->all();
		sort($owners);
		self::assertSame([$this->admin->id, $this->userMayUpload2->id], $owners);
	}

	/** Feature 115 (S-115-30): delete-missing in a shared target leaves other owners' content. */
	public function testOwnerScopeDeleteMissingLeavesOthersContent(): void
	{
		Queue::fake();
		Configs::set('import_root_folder_as_album', '0');
		$example = $this->createExampleTree();
		$bob_album = Album::factory()->children_of($this->album5)->owned_by($this->userMayUpload2)->with_title('BobAlbum')->create();
		$bob_photo = Photo::factory()->owned_by($this->userMayUpload2)->with_title('bobphoto')->in($this->album5)->create();
		$ghost = Photo::factory()->owned_by($this->admin)->with_title('ghost')->in($this->album5)->create();
		$this->album5->refresh();
		$admin_album = Album::factory()->children_of($this->album5)->owned_by($this->admin)->with_title('AdminGone')->create();
		// `children_of` hands children to the parent's owner: give it back to Bob.
		DB::table('base_albums')->where('id', '=', $bob_album->id)->update(['owner_id' => $this->userMayUpload2->id]);

		$this->sync($example, $this->album5->id, delete_missing: true);

		self::assertNotNull(DB::table('albums')->where('id', '=', $bob_album->id)->first());
		self::assertNotNull(Photo::query()->find($bob_photo->id));
		self::assertNull(Photo::query()->find($ghost->id));
		self::assertNull(DB::table('albums')->where('id', '=', $admin_album->id)->first());
		Configs::set('import_root_folder_as_album', '1');
	}

	/** Feature 115 (S-115-31): with the scope off, reuse and deletion ignore owners (pre-115 behaviour). */
	public function testOwnerScopeOff(): void
	{
		Queue::fake();
		Configs::set('import_scope_to_owner', '0');
		$example = $this->createExampleTree();
		$bob_example = Album::factory()->as_root()->owned_by($this->userMayUpload2)->with_title('Example')->create();
		$bob_photo = Photo::factory()->owned_by($this->userMayUpload2)->with_title('bobphoto')->in($bob_example)->create();

		$this->sync($example, null, delete_missing: true);

		self::assertSame(1, DB::table('base_albums')->where('title', '=', 'Example')->count());
		self::assertSame($bob_example->id, $this->queuedAlbumOf('a.jpg'));
		self::assertNull(Photo::query()->find($bob_photo->id));
		Configs::set('import_scope_to_owner', '1');
	}

	/**
	 * alice → `<base>/alice`, bob → `<base>/bob` (one image), admin → `/`.
	 *
	 * @return array{0:User,1:User}
	 */
	private function setUpUserFolders(): array
	{
		mkdir($this->import_base . '/bob');
		$this->placeImage($this->import_base . '/bob/e.jpg', 'tests/Samples/night.jpg');
		$alice = $this->restrictTo($this->userMayUpload1, $this->import_root);
		$bob = $this->restrictTo($this->userMayUpload2, $this->import_base . '/bob');
		$this->admin = $this->grantFullAccess($this->admin);

		return [$alice, $bob];
	}

	/**
	 * @return array<int,string[]> owner id => base names queued
	 */
	private function queuedPerOwner(): array
	{
		$per_owner = [];
		foreach (Queue::pushed(ImportImageJob::class) as $job) {
			$per_owner[$job->intended_owner_id][] = basename($job->file_path);
		}
		foreach ($per_owner as &$files) {
			sort($files);
		}

		return $per_owner;
	}

	/** Feature 115 (S-115-16) */
	public function testNoPathWithoutConfigFails(): void
	{
		Queue::fake();
		$this->setUpUserFolders();

		$this->artisan(self::COMMAND)
			->expectsOutputToContain('No path given. Pass paths, or enable sync_users_server_paths.')
			->assertExitCode(1);
		Queue::assertNothingPushed();
	}

	/** Feature 115 (S-115-17, S-115-34): every restricted user's folder, as that user; `/` users skipped. */
	public function testNoPathSyncsEveryUser(): void
	{
		Queue::fake();
		Configs::set('sync_users_server_paths', '1');
		[$alice, $bob] = $this->setUpUserFolders();

		$this->artisan(self::COMMAND)
			->expectsOutputToContain('skipped: full access')
			->assertExitCode(0);

		$per_owner = $this->queuedPerOwner();
		self::assertSame([$alice->id, $bob->id], array_keys($per_owner));
		self::assertSame(['a.jpg', 'b.jpg', 'c.jpg'], $per_owner[$alice->id]);
		self::assertSame(['e.jpg'], $per_owner[$bob->id]);
		Queue::assertPushed(ImportImageJob::class, fn (ImportImageJob $job) => $job->intended_owner_id !== $alice->id || $job->import_root === $this->import_root);
		self::assertNotNull($this->albumId('alice', null));
		Configs::set('sync_users_server_paths', '0');
	}

	/** Feature 115 (S-115-17): the symbolic link option applies to every user's folder. */
	public function testNoPathKeepsSymlinkOption(): void
	{
		Queue::fake();
		Configs::set('sync_users_server_paths', '1');
		$this->setUpUserFolders();

		$this->artisan(self::COMMAND, ['--import_via_symlink' => '1'])->assertExitCode(0);

		Queue::assertPushed(ImportImageJob::class);
		Queue::assertNotPushed(ImportImageJob::class, fn (ImportImageJob $job) => !$job->import_mode->shall_import_via_symlink);
		Configs::set('sync_users_server_paths', '0');
	}

	/** Feature 115 (S-115-18): a missing folder does not stop the others, exit 1. */
	public function testNoPathMissingFolder(): void
	{
		Queue::fake();
		Configs::set('sync_users_server_paths', '1');
		[$alice, $bob] = $this->setUpUserFolders();
		exec('rm -rf ' . escapeshellarg($this->import_root));

		$this->artisan(self::COMMAND)->assertExitCode(1);

		self::assertSame([$bob->id], array_keys($this->queuedPerOwner()));
		Configs::set('sync_users_server_paths', '0');
	}

	/** Feature 115 (S-115-35) */
	public function testNoPathExplicitOwner(): void
	{
		Queue::fake();
		Configs::set('sync_users_server_paths', '1');
		[, $bob] = $this->setUpUserFolders();

		$this->artisan(self::COMMAND, ['--owner_id' => $bob->id])->assertExitCode(0);

		self::assertSame([$bob->id], array_keys($this->queuedPerOwner()));
		Configs::set('sync_users_server_paths', '0');
	}

	/** Feature 115 (S-115-36) */
	public function testNoPathWithAlbumFails(): void
	{
		Queue::fake();
		Configs::set('sync_users_server_paths', '1');
		$this->setUpUserFolders();

		$this->artisan(self::COMMAND, ['--album_id' => $this->album5->id])->assertExitCode(1);
		Queue::assertNothingPushed();
		Configs::set('sync_users_server_paths', '0');
	}

	/** Feature 115 (S-115-37): users who may not upload are skipped, and refused when named. */
	public function testNoPathUserWithoutUploadRight(): void
	{
		Queue::fake();
		Configs::set('sync_users_server_paths', '1');
		[$alice, $bob] = $this->setUpUserFolders();
		$bob->may_upload = false;
		$bob->save();

		$this->artisan(self::COMMAND)
			->expectsOutputToContain('skipped: may not import')
			->assertExitCode(0);
		self::assertSame([$alice->id], array_keys($this->queuedPerOwner()));

		$this->artisan(self::COMMAND, ['--owner_id' => $bob->id])->assertExitCode(1);
		$this->artisan(self::COMMAND, ['--owner_id' => $this->admin->id])->assertExitCode(1);
		Configs::set('sync_users_server_paths', '0');
	}
}
