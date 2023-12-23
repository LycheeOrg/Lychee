<?php

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

use App\Livewire\Components\Forms\Add\ImportFromUrl;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Constants\TestConstants;
use Tests\Livewire\Base\BaseLivewireTest;

class ImportFromUrlTest extends BaseLivewireTest
{
	use DatabaseTransactions;

	public function testLoggedOut(): void
	{
		Livewire::test(ImportFromUrl::class)
			->assertForbidden();
	}

	public function testLoggedInNoUpload(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(ImportFromUrl::class)
			->assertForbidden();
	}

	public function testLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(ImportFromUrl::class)
			->assertViewIs('livewire.forms.add.import-from-url')
			->assertOk()
			->set('form.url', TestConstants::SAMPLE_DOWNLOAD_JPG)
			->call('submit')
			->assertOk()
			->assertDispatched('reloadPage')
			->assertDispatched('closeModal');
	}

	public function testLoggedInClose(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(ImportFromUrl::class)
			->assertViewIs('livewire.forms.add.import-from-url')
			->assertOk()
			->call('close')
			->assertDispatched('closeModal');
	}
}