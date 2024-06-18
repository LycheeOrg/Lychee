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

use App\Livewire\Components\Forms\Add\ImportFromDropbox;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Constants\TestConstants;
use Tests\Livewire\Base\BaseLivewireTest;

class ImportFromDropBoxTest extends BaseLivewireTest
{
	use DatabaseTransactions;

	public function testLoggedOut(): void
	{
		Livewire::test(ImportFromDropbox::class)
			->assertForbidden();
	}

	public function testLoggedInNoUpload(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(ImportFromDropbox::class)
			->assertForbidden();
	}

	public function testLoggedInUpload(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(ImportFromDropbox::class)
			->assertForbidden();
	}

	public function testLoggedInAdmin(): void
	{
		Livewire::actingAs($this->admin)->test(ImportFromDropbox::class)
			->assertViewIs('livewire.forms.add.import-from-dropbox')
			->assertOk()
			->set('form.urlArea', TestConstants::SAMPLE_DOWNLOAD_JPG)
			->call('submit')
			->assertOk()
			->assertDispatched('reloadPage')
			->assertDispatched('closeModal');
	}

	public function testLoggedInClose(): void
	{
		Livewire::actingAs($this->admin)->test(ImportFromDropbox::class)
			->assertViewIs('livewire.forms.add.import-from-dropbox')
			->assertOk()
			->call('close')
			->assertDispatched('closeModal');
	}
}