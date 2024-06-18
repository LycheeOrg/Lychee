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

namespace Tests\Livewire\Forms\Profile;

use App\Enum\OauthProvidersType;
use App\Livewire\Components\Forms\Profile\Oauth;
use App\Models\OauthCredential;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class OauthTest extends BaseLivewireTest
{
	private string $component = Oauth::class;

	public function testLoggedInOauthListing(): void
	{
		Config::set('services.amazon.client_id', 'FAKE');
		Config::set('services.github.client_id', 'FAKE');

		Livewire::actingAs($this->userMayUpload1)->test($this->component)
			->assertViewIs('livewire.forms.profile.oauth')
			->assertSet('oauthData.amazon.isEnabled', false)
			->assertCount('oauthData', 2);
	}

	public function testLoggedInOauthListingWithOauth(): void
	{
		Config::set('services.amazon.client_id', 'FAKE');
		Config::set('services.github.client_id', 'FAKE');

		OauthCredential::create([
			'provider' => OauthProvidersType::AMAZON,
			'token_id' => 'something',
			'user_id' => $this->userMayUpload1->id])->save();

		Livewire::actingAs($this->userMayUpload1)->test($this->component)
			->assertViewIs('livewire.forms.profile.oauth')
			->assertSet('oauthData.amazon.isEnabled', true)
			->assertSet('oauthData.github.isEnabled', false)
			->assertCount('oauthData', 2);
	}

	public function testLoggedInOauthListingWithOauthClear(): void
	{
		Config::set('services.amazon.client_id', 'FAKE');
		Config::set('services.github.client_id', 'FAKE');

		OauthCredential::create([
			'provider' => OauthProvidersType::AMAZON,
			'token_id' => 'something',
			'user_id' => $this->userMayUpload1->id])->save();

		Livewire::actingAs($this->userMayUpload1)->test($this->component)
			->assertViewIs('livewire.forms.profile.oauth')
			->assertSet('oauthData.amazon.isEnabled', true)
			->assertSet('oauthData.github.isEnabled', false)
			->assertCount('oauthData', 2)
			->call('clear', 'amazon')
			->assertSet('oauthData.amazon.isEnabled', false);
	}

	public function testLoggedOutOauthListingFails(): void
	{
		Config::set('services.amazon.client_id', 'FAKE');
		Config::set('services.github.client_id', 'FAKE');

		Livewire::test($this->component)
			->assertForbidden();
	}
}
