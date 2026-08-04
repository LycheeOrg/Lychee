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

namespace Tests\Unit\Jobs;

use App\Enum\JobStatus;
use App\Exceptions\ZipInvalidException;
use App\Image\Files\ProcessableJobFile;
use App\Jobs\ExtractZip;
use App\Models\Album;
use App\Models\JobHistory;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

/**
 * Only the constructor and `validate_zip()` (security pre-flight checks) are
 * covered here. `handle()` beyond that point instantiates `App\Actions\Import\Exec`
 * directly (not injected) and performs a real import, so it is not
 * unit-testable without exercising the full image pipeline.
 */
class ExtractZipTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private string $zip_path;

	protected function tearDown(): void
	{
		if (isset($this->zip_path) && file_exists($this->zip_path)) {
			unlink($this->zip_path);
		}
		parent::tearDown();
	}

	private function makeFile(): ProcessableJobFile
	{
		$this->zip_path = sys_get_temp_dir() . '/lychee-extractzip-test-' . uniqid() . '.zip';
		$zip = new \ZipArchive();
		$zip->open($this->zip_path, \ZipArchive::CREATE);
		$zip->addFromString('photo.jpg', 'fake-image-bytes');
		$zip->close();

		$file = \Mockery::mock(ProcessableJobFile::class);
		$file->shouldReceive('getPath')->andReturn($this->zip_path);
		$file->shouldReceive('getOriginalBasename')->andReturn('archive.zip');

		return $file;
	}

	private function callValidateZip(ExtractZip $job): void
	{
		$method = new \ReflectionMethod(ExtractZip::class, 'validate_zip');
		$method->invoke($job);
	}

	private function mockGenerousZipBombLimits(): void
	{
		$this->mock(ConfigManager::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getValueAsByteSize')->with('zip_bomb_max_total_size')->andReturn(10_000_000);
			$mock->shouldReceive('getValueAsByteSize')->with('zip_bomb_max_file_size')->andReturn(10_000_000);
			$mock->shouldReceive('getValueAsInt')->with('zip_bomb_max_entries')->andReturn(1000);
			$mock->shouldReceive('getValueAsInt')->with('zip_bomb_max_ratio')->andReturn(1000);
		});
	}

	public function testConstructorResolvesStringAlbumIdAndUser(): void
	{
		$user = User::factory()->create();
		Auth::shouldReceive('user')->once()->andReturn($user);

		$job = new ExtractZip($this->makeFile(), 'some-album-id-000000000', null);

		self::assertEquals('some-album-id-000000000', $job->album_id);
		self::assertEquals($user->id, $job->user_id);

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertNotNull($history);
		self::assertStringContainsString('archive.zip', $history->job);
	}

	public function testConstructorResolvesAbstractAlbumInstance(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		Auth::shouldReceive('user')->once()->andReturn($user);

		$job = new ExtractZip($this->makeFile(), $album, null);

		self::assertEquals($album->id, $job->album_id);
	}

	public function testConstructorAllowsNullAlbum(): void
	{
		$user = User::factory()->create();
		Auth::shouldReceive('user')->once()->andReturn($user);

		$job = new ExtractZip($this->makeFile(), null, null);

		self::assertNull($job->album_id);
	}

	public function testValidateZipPassesForSafeArchive(): void
	{
		$user = User::factory()->create();
		Auth::shouldReceive('user')->once()->andReturn($user);
		$this->mockGenerousZipBombLimits();

		$job = new ExtractZip($this->makeFile(), null, null);
		$this->callValidateZip($job);
		self::assertTrue(true);
	}

	public function testValidateZipRejectsZipSlipEntries(): void
	{
		$user = User::factory()->create();
		Auth::shouldReceive('user')->once()->andReturn($user);

		$this->zip_path = sys_get_temp_dir() . '/lychee-extractzip-slip-' . uniqid() . '.zip';
		$zip = new \ZipArchive();
		$zip->open($this->zip_path, \ZipArchive::CREATE);
		$zip->addFromString('../../etc/evil.txt', 'malicious');
		$zip->close();

		$file = \Mockery::mock(ProcessableJobFile::class);
		$file->shouldReceive('getPath')->andReturn($this->zip_path);
		$file->shouldReceive('getOriginalBasename')->andReturn('slip.zip');

		$job = new ExtractZip($file, null, null);

		$this->assertThrows(fn () => $this->callValidateZip($job), ZipInvalidException::class);

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}

	public function testValidateZipRejectsArchiveExceedingBombLimitsAndKeepsFileByDefault(): void
	{
		$user = User::factory()->create();
		Auth::shouldReceive('user')->once()->andReturn($user);

		$this->mock(ConfigManager::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getValueAsByteSize')->with('zip_bomb_max_total_size')->andReturn(10_000_000);
			$mock->shouldReceive('getValueAsByteSize')->with('zip_bomb_max_file_size')->andReturn(10_000_000);
			// Zero max entries guarantees rejection for any non-empty archive.
			$mock->shouldReceive('getValueAsInt')->with('zip_bomb_max_entries')->andReturn(0);
			$mock->shouldReceive('getValueAsInt')->with('zip_bomb_max_ratio')->andReturn(1000);
			$mock->shouldReceive('getValueAsBool')->with('zip_bomb_delete_rejected_file')->andReturn(false);
		});

		$job = new ExtractZip($this->makeFile(), null, null);
		$path = $job->file_path;

		$this->assertThrows(fn () => $this->callValidateZip($job), ZipInvalidException::class);

		self::assertFileExists($path);
	}

	public function testValidateZipDeletesRejectedFileWhenConfigured(): void
	{
		$user = User::factory()->create();
		Auth::shouldReceive('user')->once()->andReturn($user);

		$this->mock(ConfigManager::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getValueAsByteSize')->with('zip_bomb_max_total_size')->andReturn(10_000_000);
			$mock->shouldReceive('getValueAsByteSize')->with('zip_bomb_max_file_size')->andReturn(10_000_000);
			$mock->shouldReceive('getValueAsInt')->with('zip_bomb_max_entries')->andReturn(0);
			$mock->shouldReceive('getValueAsInt')->with('zip_bomb_max_ratio')->andReturn(1000);
			$mock->shouldReceive('getValueAsBool')->with('zip_bomb_delete_rejected_file')->andReturn(true);
		});

		$job = new ExtractZip($this->makeFile(), null, null);
		$path = $job->file_path;

		$this->assertThrows(fn () => $this->callValidateZip($job), ZipInvalidException::class);

		self::assertFileDoesNotExist($path);
	}
}
