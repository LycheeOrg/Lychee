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

use App\Livewire\Components\Forms\Add\ImportFromServer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Constants\TestConstants;
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

	public function testLoggedInAndError(): void
	{
		Livewire::actingAs($this->admin)->test(ImportFromServer::class)
			->assertViewIs('livewire.forms.add.import-from-server')
			->assertOk()
			->set('form.path', '')
			->call('submit')
			->assertOk()
			->assertHasErrors('form.paths.0');
	}

	// public function testLoggedInWithDeleteImported() : void {
	// 	copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), static::importPath('night.jpg'));

	// 	Livewire::actingAs($this->admin)->test(ImportFromServer::class)
	// 		->assertViewIs('livewire.forms.add.import-from-server')
	// 		->assertOk()
	// 		->set('form.path', static::importPath())
	// 		->assertSet('form.delete_imported', false)
	// 		->call('submit')
	// 		->assertOk()
	// 		->assertHasNoErrors('form.paths.0');
	
	// 	$this->assertEquals(true, file_exists(static::importPath('night.jpg')));
	// }

	// public function testLoggedInWithoutDeleteImported() : void {
	// 	copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), static::importPath('night.jpg'));

	// 	Livewire::actingAs($this->admin)->test(ImportFromServer::class)
	// 		->assertViewIs('livewire.forms.add.import-from-server')
	// 		->assertOk()
	// 		->set('form.path', static::importPath())
	// 		->set('form.delete_imported', true)
	// 		->assertSet('form.delete_imported', true)
	// 		->call('submit')
	// 		->assertOk()
	// 		->assertHasNoErrors('form.paths.0');
	
	// 	$this->assertEquals(false, file_exists(static::importPath('night.jpg')));
	// }

	public function testLoggedInClose(): void
	{
		Livewire::actingAs($this->admin)->test(ImportFromServer::class)
			->assertViewIs('livewire.forms.add.import-from-server')
			->assertOk()
			->call('close')
			->assertDispatched('closeModal');
	}
}