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

namespace Tests\Livewire\Pages;

use App\Livewire\Components\Pages\Login;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class LoginTest extends BaseLivewireTest
{
	/**
	 * Test that when we are not logged in,
	 * and login is required, we are properly redirected.
	 *
	 * @return void
	 */
	public function testLoggedOutRedirect(): void
	{
		Configs::set('login_required', '1');

		$response = $this->get(route('livewire-gallery'));
		$this->assertRedirect($response);
		$response->assertRedirect(route('login'));

		Configs::set('login_required', '0');
	}

	/**
	 * Test that when we are not logged in,
	 * and login is NOT required, we are not redirected.
	 *
	 * @return void
	 */
	public function testLoggedOutNoRedirect(): void
	{
		Configs::set('login_required', '0');

		$response = $this->get(route('livewire-gallery'));
		$this->assertOk($response);

		Configs::set('login_required', '0');
	}

	/**
	 * Test that when we are logged in,
	 * and login is required, we are not redirected.
	 *
	 * @return void
	 */
	public function testLoggedInNoRedirect(): void
	{
		Auth::login($this->admin);

		Configs::set('login_required', '1');

		$response = $this->get(route('livewire-gallery'));
		$this->assertOk($response);

		Configs::set('login_required', '0');
	}

	/**
	 * Test that when we are logged in,
	 * and we are properly redirected when accessing login page.
	 *
	 * @return void
	 */
	public function testLoginRedirect(): void
	{
		Livewire::actingAs($this->admin)->test(Login::class)
			->assertRedirect(route('livewire-gallery'));
	}
}
