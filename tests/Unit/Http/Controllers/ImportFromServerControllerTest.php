<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Unit\Http\Controllers;

use App\Actions\Import\Exec;
use App\Exceptions\EmptyFolderException;
use App\Exceptions\InvalidDirectoryException;
use App\Exceptions\InvalidOptionsException;
use App\Exceptions\UnexpectedException;
use App\Http\Controllers\Admin\ImportFromServerController;
use App\Http\Requests\Admin\ImportFromServerRequest;
use App\Http\Resources\Admin\ImportFromServerResource;
use App\Models\Album;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\AbstractTestCase;

/**
 * Unit tests for ImportFromServerController.
 *
 * Tests the import functionality including:
 * - Directory validation
 * - Import configuration
 * - Job dispatching
 * - Error handling for various scenarios
 * - Resource creation
 */
class ImportFromServerControllerTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private User $admin;
	private string $valid_dir;

	public function setUp(): void
	{
		parent::setUp();

		$this->admin = User::factory()->may_administrate()->create();

		$this->valid_dir = '/tmp/empty/folder';
		if (!is_dir($this->valid_dir)) {
			mkdir($this->valid_dir, 0755, true);
		}

		Configs::set('renamer_photo_title_enabled', false);
		Configs::set('renamer_album_title_enabled', false);
		Configs::set('owner_id', $this->admin->id);
	}

	public function tearDown(): void
	{
		// Cleanup
		rmdir($this->valid_dir);

		$this->admin->delete();
		parent::tearDown();
	}

	/**
	 * Create a mock ImportFromServerRequest for testing.
	 */
	private function createMockImportRequest(array $params, ?Album $album = null)
	{
		return new class($params, $album) extends ImportFromServerRequest {
			private array $params;
			private ?Album $targetAlbum;

			public function __construct(array $params, ?Album $album = null)
			{
				$this->params = $params;
				$this->targetAlbum = $album;

				// Set public properties
				$this->directories = $params['directories'];
				$this->delete_imported = $params['delete_imported'];
				$this->skip_duplicates = $params['skip_duplicates'];
				$this->import_via_symlink = $params['import_via_symlink'];
				$this->resync_metadata = $params['resync_metadata'];
				$this->delete_missing_photos = $params['delete_missing_photos'];
				$this->delete_missing_albums = $params['delete_missing_albums'];
			}

			public function authorize(): bool
			{
				return true; // Always authorize for tests
			}

			public function album(): ?Album
			{
				return $this->targetAlbum;
			}

			public function rules(): array
			{
				return []; // Skip validation for tests
			}
		};
	}

	/**
	 * Create a mocked version to validate get_exec.
	 *
	 * @return ImportFromServerController
	 */
	private function getMockedController(?Exec $exec = null): ImportFromServerController
	{
		if ($exec === null) {
			$exec = $this->createMock(Exec::class);
			$exec->method('do')->willReturn([]);
		}

		return new class($exec) extends ImportFromServerController {
			public function __construct(private Exec $exec)
			{
			}

			protected function get_exec(ImportFromServerRequest $request): Exec
			{
				return $this->exec;
			}
		};
	}

	/**
	 * Test successful import from multiple valid directories.
	 */
	public function testSuccessfulImportFromValidDirectories(): void
	{
		// Arrange
		Queue::fake();

		// Create controller with mock get_exec
		$controller = $this->getMockedController();

		$request = $this->createMockImportRequest([
			'directories' => [$this->valid_dir],
			'delete_imported' => false,
			'skip_duplicates' => true,
			'import_via_symlink' => false,
			'resync_metadata' => false,
			'delete_missing_photos' => false,
			'delete_missing_albums' => false,
		]);

		// Act
		$result = $controller->__invoke($request);

		// Assert
		$this->assertInstanceOf(ImportFromServerResource::class, $result);
		$this->assertTrue($result->status);
		$this->assertEquals('Import process completed', $result->message);
		$this->assertCount(1, $result->results);
		$this->assertEquals($this->valid_dir, $result->results[0]->directory);

		// Cleanup
	}

	/**
	 * Test exception when directory doesn't exist.
	 */
	public function testInvalidOptionsExceptionWhenDirectoryDoesNotExist(): void
	{
		// Arrange
		$nonExistentDir = '/tmp/non_existent_directory_12345';

		$request = $this->createMockImportRequest([
			'directories' => [$nonExistentDir],
			'delete_imported' => false,
			'skip_duplicates' => false,
			'import_via_symlink' => false,
			'resync_metadata' => false,
			'delete_missing_photos' => false,
			'delete_missing_albums' => false,
		]);

		$this->expectException(InvalidOptionsException::class);
		$this->expectExceptionMessage('directory does not exists: ' . $nonExistentDir);

		// Act
		(new ImportFromServerController())->__invoke($request);
	}

	/**
	 * Test handling of empty folder exception.
	 */
	public function testHandleEmptyFolderException(): void
	{
		$exec = $this->createMock(Exec::class);
		$exec->method('do')->willThrowException(new EmptyFolderException('/test/empty/folder'));
		$controller = $this->getMockedController($exec);

		// Create mock request
		$mockRequest = $this->createMockImportRequest([
			'directories' => [$this->valid_dir],
			'delete_imported' => true,
			'skip_duplicates' => false,
			'import_via_symlink' => false,
			'resync_metadata' => true,
			'delete_missing_photos' => true,
			'delete_missing_albums' => true,
		]);

		// Act - Call the controller method
		$result = $controller($mockRequest);

		// Assert
		$this->assertInstanceOf(ImportFromServerResource::class, $result);
		$this->assertTrue($result->status);
		$this->assertCount(1, $result->results);
		$this->assertFalse($result->results[0]->status);
		$this->assertStringContainsString('Empty folder: /test/empty/folder is empty', $result->results[0]->message);
		$this->assertEquals($this->valid_dir, $result->results[0]->directory);
	}

	/**
	 * Test handling of invalid directory exception.
	 */
	public function testHandleInvalidDirectoryException(): void
	{
		// Create controller with mock get_exec that throws InvalidDirectoryException
		$exec = $this->createMock(Exec::class);
		$exec->method('do')->willThrowException(new InvalidDirectoryException());
		$controller = $this->getMockedController($exec);

		// Create mock request
		$mockRequest = $this->createMockImportRequest([
			'directories' => [$this->valid_dir],
			'delete_imported' => true,
			'skip_duplicates' => false,
			'import_via_symlink' => false,
			'resync_metadata' => true,
			'delete_missing_photos' => true,
			'delete_missing_albums' => true,
		]);

		// Act - Call the controller method
		$result = $controller($mockRequest);

		// Assert
		$this->assertInstanceOf(ImportFromServerResource::class, $result);
		$this->assertTrue($result->status);
		$this->assertCount(1, $result->results);
		$this->assertFalse($result->results[0]->status);
		$this->assertStringContainsString('Invalid directory: Given path is not a directory', $result->results[0]->message);
		$this->assertEquals($this->valid_dir, $result->results[0]->directory);
	}

	/**
	 * Test handling of unexpected exception.
	 */
	public function testHandleUnexpectedException(): void
	{
		$exec = $this->createMock(Exec::class);
		$exec->method('do')->willThrowException(new UnexpectedException());
		$controller = $this->getMockedController($exec);

		// Create mock request
		$mockRequest = $this->createMockImportRequest([
			'directories' => [$this->valid_dir],
			'delete_imported' => true,
			'skip_duplicates' => false,
			'import_via_symlink' => false,
			'resync_metadata' => true,
			'delete_missing_photos' => true,
			'delete_missing_albums' => true,
		]);

		// Act - Call the controller method
		$this->expectException(UnexpectedException::class);
		$controller($mockRequest);
	}

	/**
	 * Test multiple directories with mixed success/failure scenarios.
	 */
	public function testMultipleDirectoriesWithMixedResults(): void
	{
		// Arrange
		Queue::fake();

		$validDir = '/tmp/test_import_valid';
		$invalidDir = '/tmp/non_existent_directory_mixed';

		if (!is_dir($validDir)) {
			mkdir($validDir, 0755, true);
		}
		// Don't create invalidDir to test mixed results

		$request = $this->createMockImportRequest([
			'directories' => [$validDir, $invalidDir],
			'delete_imported' => false,
			'skip_duplicates' => false,
			'import_via_symlink' => false,
			'resync_metadata' => false,
			'delete_missing_photos' => false,
			'delete_missing_albums' => false,
		]);

		$this->expectException(InvalidOptionsException::class);

		// Act - This should fail because one directory doesn't exist
		(new ImportFromServerController())->__invoke($request);

		// Cleanup
		rmdir($validDir);
	}
}