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

use App\Livewire\Components\Forms\Profile\GetApiToken;
use App\Livewire\Components\Forms\Profile\ManageSecondFactor;
use App\Livewire\Components\Forms\Profile\SecondFactor;
use App\Livewire\Components\Forms\Profile\SetEmail;
use App\Livewire\Components\Forms\Profile\SetLogin;
use App\Models\User;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\Feature\Traits\RequiresEmptyWebAuthnCredentials;
use Tests\Livewire\Base\BaseLivewireTest;

class FormsProfileTest extends BaseLivewireTest
{
	use RequiresEmptyUsers;
	use RequiresEmptyWebAuthnCredentials;

	private User $user;
	private User $user2;
	private WebAuthnCredential $credential;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyWebAuthnCredentials();
		/** @var User $user */
		$user = User::factory()->create();
		$this->user = $user;
		/** @var User $user */
		$user = User::factory()->create();
		$this->user2 = $user;

		$this->credential = $this->user->makeWebAuthnCredential([
			'id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA',

			'user_id' => '27117450ff81461d80331fb79c655f39',
			'alias' => null,

			'counter' => 0,
			'rp_id' => 'https://localhost',
			'origin' => 'https://localhost',
			'transports' => null,
			'aaguid' => '00000000-0000-0000-0000-000000000000',

			'public_key' => "-----BEGIN PUBLIC KEY-----\nMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEF25MWDQwaeFqZJ2Gy/7HEdZkWaW+\nQCbWjLiplbklmqIq6MSSRhLqJLoegR5PqG2JOqhSLcQDAmf/tzdAvO5MmQ==\n-----END PUBLIC KEY-----\n",
			'attestation_format' => 'none',
		]);
		$this->credential->save();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebAuthnCredentials();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	public function testSecondFactorLoggedOut(): void
	{
		Livewire::test(SecondFactor::class)->assertForbidden();
	}

	public function testSecondFactorLoggedIn(): void
	{
		$this->assertEquals(1, $this->user->webAuthnCredentials()->count());
		Livewire::actingAs($this->user)->test(SecondFactor::class)
			->assertViewIs('livewire.modules.profile.second-factor')
			->assertSeeLivewire(ManageSecondFactor::class)
			->dispatch('reload-component') // called when a WebAuthn is create
			->call('delete', '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA')
			->assertDontSeeLivewire(ManageSecondFactor::class);

		$this->assertEquals(0, $this->user->webAuthnCredentials()->count());
	}

	public function testSecondFactorLoggedInCrossInteraction(): void
	{
		$this->assertEquals(1, $this->user->webAuthnCredentials()->count());
		$this->assertEquals(0, $this->user2->webAuthnCredentials()->count());
		Livewire::actingAs($this->user2)->test(SecondFactor::class)
			->assertViewIs('livewire.modules.profile.second-factor')
			->assertDontSeeLivewire(ManageSecondFactor::class)
			->dispatch('reload-component') // called when a WebAuthn is create
			->call('delete', '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA')
			->assertDontSeeLivewire(ManageSecondFactor::class);

		$this->assertEquals(1, $this->user->webAuthnCredentials()->count());
	}

	public function testManageSecondFactorLogout(): void
	{
		Livewire::test(ManageSecondFactor::class)->assertForbidden();
	}

	public function testManageSecondFactorLoginCrossInteraction(): void
	{
		Livewire::actingAs($this->user2)->test(ManageSecondFactor::class, ['credential' => $this->credential])
			->assertForbidden();
	}

	public function testManageSecondFactorLogin(): void
	{
		Livewire::actingAs($this->user)->test(ManageSecondFactor::class, ['credential' => $this->credential])
			->assertViewIs('livewire.forms.profile.manage-second-factor')
			->assertSet('alias', '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp')
			->set('alias', '123')
			->assertSet('alias', '123')
			->assertDispatched('notify');

		// reload model
		$this->credential->fresh();

		Livewire::actingAs($this->user)->test(ManageSecondFactor::class, ['credential' => $this->credential])
			->assertViewIs('livewire.forms.profile.manage-second-factor')
			->assertSet('alias', '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp')
			->set('alias', '12345')
			->assertSet('alias', '12345')
			->assertDispatched('notify');

		$this->assertEquals('12345', $this->credential->fresh()->alias);
	}

	public function testGetApiTokenLogout(): void
	{
		Livewire::test(GetApiToken::class)
			->assertUnauthorized();
	}

	public function testSetEmailLogout(): void
	{
		Livewire::test(SetEmail::class)
			->assertForbidden();
	}

	public function testSetLoginLogout(): void
	{
		Livewire::test(SetLogin::class)
			->assertForbidden();
	}

	public function testGetApiTokenLogin(): void
	{
		Livewire::actingAs($this->user)->test(GetApiToken::class)
			->assertViewIs('livewire.forms.profile.get-api-token')
			->assertSet('token', __('lychee.DISABLED_TOKEN_STATUS_MSG'))
			->call('resetToken')
			->assertNotSet('token', __('lychee.DISABLED_TOKEN_STATUS_MSG'))
			->call('close')
			->assertDispatched('closeModal');

		Livewire::actingAs($this->user)->test(GetApiToken::class)
			->assertViewIs('livewire.forms.profile.get-api-token')
			->assertSet('token', __('lychee.TOKEN_NOT_AVAILABLE'))
			->call('disableToken')
			->assertSet('token', __('lychee.DISABLED_TOKEN_STATUS_MSG'));

		$this->assertNull($this->user->fresh()->token);
	}

	public function testSetEmailLogin(): void
	{
		Livewire::actingAs($this->user)->test(SetEmail::class)
			->assertViewIs('livewire.forms.settings.input')
			->set('value', 'user@lychee.org')
			->call('save')
			->assertOk();

		$this->assertEquals('user@lychee.org', $this->user->fresh()->email);
	}

	public function testSetLoginLogin(): void
	{
		Livewire::actingAs($this->user)->test(SetLogin::class)
			->assertViewIs('livewire.forms.profile.set-login')
			->call('openApiTokenModal')
			->assertDispatched('openModal', 'forms.profile.get-api-token')
			->set('oldPassword', '')
			->set('password', '')
			->set('password_confirmation', '')
			->set('username', '')
			->call('submit')
			->assertHasErrors('oldPassword')
			->set('oldPassword', 'password')
			->call('submit')
			->assertHasErrors('password')
			->set('username', 'user2')
			->set('password', 'password')
			->call('submit')
			->assertHasErrors('password')
			->set('password_confirmation', 'password')
			->call('submit')
			->assertHasNoErrors('password');

		$this->assertEquals('user2', $this->user->fresh()->username());
	}
}
