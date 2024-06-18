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

namespace Tests\Livewire\Modals;

use App\Livewire\Components\Base\Modal;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class ModalTest extends BaseLivewireTest
{
	public function testOpenClose(): void
	{
		Livewire::test(Modal::class)
			->assertSet('isOpen', false)
			->dispatch('openModal', 'modals.about', __('lychee.CLOSE'))
			->assertSet('isOpen', true)
			->assertSet('type', 'modals.about')
			->assertSet('close_text', __('lychee.CLOSE'))
			->assertViewIs('livewire.components.modal')
			->dispatch('closeModal')
			->assertSet('isOpen', false);
	}

	public function testOpenClose2(): void
	{
		Livewire::test(Modal::class)
			->assertSet('isOpen', false)
			->call('openModal', 'modals.about', __('lychee.CLOSE'))
			->assertSet('isOpen', true)
			->assertSet('type', 'modals.about')
			->assertSet('close_text', __('lychee.CLOSE'))
			->assertViewIs('livewire.components.modal')
			->dispatch('closeModal')
			->assertSet('isOpen', false);
	}
}
