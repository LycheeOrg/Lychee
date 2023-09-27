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

namespace Tests\Livewire\Forms\Album;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Forms\Album\Create;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class CreateTest extends BaseLivewireTest
{
	public function testCreateLoggedOut(): void
	{
		Livewire::test(Create::class, ['params' => [Params::PARENT_ID => null]])
			->assertForbidden();

		Livewire::test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertForbidden();
	}

	public function testCreateLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Create::class, ['params' => [Params::PARENT_ID => null]])
			->assertForbidden();

		Livewire::actingAs($this->userNoUpload)->test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertForbidden();
	}

	public function testCreateLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.add.create')
			->call('close')
			->assertDispatched('closeModal');

		Livewire::actingAs($this->userMayUpload1)->test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.add.create')
			->set('title', fake()->country() . ' ' . fake()->year())
			->call('submit')
			->assertRedirect();

		$this->assertCount(2, $this->album1->fresh()->load('children')->children);
	}
}
