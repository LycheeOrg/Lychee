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

namespace Tests\Livewire\Forms\Album;

use App\Livewire\Components\Forms\Album\CreateTag;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class CreateTagTest extends BaseLivewireTest
{
	public function testCreateLoggedOut(): void
	{
		Livewire::test(CreateTag::class)
			->assertForbidden();
	}

	public function testCreateLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(CreateTag::class)
			->assertForbidden();
	}

	public function testCreateLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(CreateTag::class)
			->assertOk()
			->assertViewIs('livewire.forms.add.create-tag')
			->call('close')
			->assertDispatched('closeModal');

		Livewire::actingAs($this->userMayUpload1)->test(CreateTag::class)
			->assertOk()
			->assertViewIs('livewire.forms.add.create-tag')
			->set('title', fake()->country() . ' ' . fake()->year())
			->set('tag', 'something')
			->call('submit')
			->assertRedirect();
	}
}
