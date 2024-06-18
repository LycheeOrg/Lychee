<?php

declare(strict_types=1);

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Livewire\Forms;

use App\Livewire\Components\Forms\Add\Upload;
use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use function Safe\copy;
use function Safe\file_get_contents;
use function Safe\filesize;
use function Safe\tempnam;
use Tests\Feature\Constants\TestConstants;
use Tests\Livewire\Base\BaseLivewireTest;

class UploadTest extends BaseLivewireTest
{
	private int $upload_chunk_size;
	private int $upload_processing_limit;

	public function setUp(): void
	{
		parent::setUp();
		$this->upload_chunk_size = Configs::getValueAsInt('upload_chunk_size');
		$this->upload_processing_limit = Configs::getValueAsInt('upload_processing_limit');
		Configs::set('upload_processing_limit', 57);
		Configs::set('upload_chunk_size', 12345);
	}

	public function tearDown(): void
	{
		Configs::set('upload_processing_limit', $this->upload_processing_limit);
		Configs::set('upload_chunk_size', $this->upload_chunk_size);
		parent::tearDown();
	}

	public function testConfirmLoggedOut(): void
	{
		Livewire::test(Upload::class)->assertForbidden();
	}

	public function testLoggedInNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Upload::class)
			->assertForbidden();
	}

	public function testConfirmLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Upload::class)
			->assertOk()
			->assertViewIs('livewire.forms.add.upload')
			->assertSet('chunkSize', 12345)
			->assertSet('parallelism', 57)
			->call('close')
			->assertDispatched('closeModal');

		// Reset
		Configs::set('upload_chunk_size', 0);

		$tmpFilename = tempnam(sys_get_temp_dir(), 'lychee');
		copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), $tmpFilename);
		$uploadedFile = UploadedFile::fake()->createWithContent('night.jpg', file_get_contents($tmpFilename));
		$name = TestConstants::SAMPLE_FILE_NIGHT_IMAGE;
		$size = filesize($tmpFilename);

		Livewire::actingAs($this->userMayUpload1)->test(Upload::class)
			->assertOk()
			->assertViewIs('livewire.forms.add.upload')
			->set([
				'uploads.0.fileName' => $name,
				'uploads.0.fileSize' => $size,
				'uploads.0.lastModified' => 0,
				'uploads.0.stage' => 'uploading',
				'uploads.0.progress' => 0,
			])
			->set('uploads.0.fileChunk', $uploadedFile)
			->assertOk();
	}
}
