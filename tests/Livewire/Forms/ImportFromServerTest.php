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

use App\Livewire\Components\Forms\Add\ImportFromServer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class ImportFromServerTest extends BaseLivewireTest
{
	use DatabaseTransactions;

	public function testLoggedOut(): void
	{
		Livewire::test(ImportFromServer::class)
			->assertForbidden();
	}

	public function testLoggedInNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(ImportFromServer::class)
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload1)->test(ImportFromServer::class)
			->assertForbidden();
	}

	public function testLoggedInClose(): void
	{
		Livewire::actingAs($this->admin)->test(ImportFromServer::class)
			->assertViewIs('livewire.forms.add.import-from-server')
			->assertOk()
			->call('close')
			->assertDispatched('closeModal');
	}
}