<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\GalleryPassword;

use App\Exceptions\GalleryPasswordRequiredException;
use App\Models\Configs;
use App\Services\GalleryLockState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 074 – global gallery password: gate, unlock endpoint, RSS/embed disabling.
 */
class GalleryPasswordTest extends BaseApiWithDataTest
{
	private const PASSWORD = 'open sesame';

	private string $stored_hash = '';

	/** @var array<string,string|int|bool|null> */
	private array $initial_configs = [];

	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		foreach (['gallery_password', 'gallery_password_cookie_lifetime', 'login_required', 'rss_enable', 'is_embed_enabled'] as $key) {
			$this->initial_configs[$key] = Configs::query()->where('key', '=', $key)->value('value');
		}
		$this->stored_hash = Hash::make(self::PASSWORD);
	}

	public function tearDown(): void
	{
		foreach ($this->initial_configs as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value ?? '']);
		}
		resolve(\App\Repositories\ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	private function setGalleryPassword(): void
	{
		Configs::set('gallery_password', $this->stored_hash);
	}

	private function withUnlockCookie(?int $expires_at): static
	{
		return $this->withCookie(GalleryLockState::COOKIE_NAME, resolve(GalleryLockState::class)->makeCookieValue($this->stored_hash, $expires_at));
	}

	private function assertGalleryLocked(TestResponse $response): void
	{
		$this->assertUnauthorized($response);
		$response->assertJson(['message' => GalleryPasswordRequiredException::DEFAULT_MESSAGE]);
	}

	// ── Gate (S-074-01, 02, 07, 08, 13, 16) ──────────────────────

	public function testNoPasswordBehavesAsBefore(): void
	{
		$this->assertOk($this->getJson('Albums'));
		$this->assertOk($this->getJsonV3('Albums/root'));
	}

	public function testLockedWithoutUnlock(): void
	{
		$this->setGalleryPassword();

		$this->assertGalleryLocked($this->getJson('Albums'));
		$this->assertGalleryLocked($this->getJsonWithData('Album::head', ['album_id' => $this->album4->id]));
		$this->assertGalleryLocked($this->getJson('Timeline'));
		$this->assertGalleryLocked($this->getJsonV3('Albums/root'));
	}

	public function testLockedDoesNotRevealAlbumExistence(): void
	{
		$this->setGalleryPassword();

		$this->assertGalleryLocked($this->getJsonWithData('Album::head', ['album_id' => 'aaaaaaaaaaaaaaaaaaaaaaaa']));
	}

	public function testUnlockedWithValidCookie(): void
	{
		$this->setGalleryPassword();

		$this->assertOk($this->withUnlockCookie(time() + 60)->getJson('Albums'));
		$this->assertOk($this->withUnlockCookie(null)->getJsonV3('Albums/root'));
	}

	public function testLockedWithExpiredOrTamperedCookie(): void
	{
		$this->setGalleryPassword();

		$this->assertGalleryLocked($this->withUnlockCookie(time() - 1)->getJson('Albums'));
		$this->assertGalleryLocked($this->withCookie(GalleryLockState::COOKIE_NAME, '{"f":"forged","exp":null}')->getJson('Albums'));
	}

	public function testLoggedInUserIsNeverAsked(): void
	{
		$this->setGalleryPassword();

		$this->assertOk($this->actingAs($this->userMayUpload1)->getJson('Albums'));
		$this->assertOk($this->actingAs($this->userMayUpload1)->getJsonV3('Albums/root', ['scope' => 'own']));
	}

	public function testLoginStaysReachable(): void
	{
		$this->setGalleryPassword();

		$this->assertOk($this->getJson('Gallery::Init'));
		$this->assertOk($this->getJson('Auth::config'));
		$this->assertOk($this->getJson('Auth::user'));
		$this->assertOk($this->getJson('LandingPage'));
		$this->assertNoContent($this->postJson('Auth::login', ['username' => $this->userMayUpload1->username, 'password' => 'password']));
		$this->assertOk($this->getJson('Albums'));
	}

	public function testAiCallbackIsNotGated(): void
	{
		$this->setGalleryPassword();

		$response = $this->postJson('FaceDetection/results', []);
		self::assertNotSame(GalleryPasswordRequiredException::DEFAULT_MESSAGE, $response->json('message'));
	}

	public function testLoginRequiredStillAppliesAfterUnlock(): void
	{
		$this->setGalleryPassword();
		Configs::set('login_required', '1');

		$response = $this->withUnlockCookie(time() + 60)->getJson('Albums');
		$this->assertUnauthorized($response);
		$response->assertJson(['message' => 'Login required.']);
	}

	public function testGatedRequestsNeverRunBcrypt(): void
	{
		$this->setGalleryPassword();
		Hash::spy();

		for ($i = 0; $i < 20; $i++) {
			$this->assertOk($this->withUnlockCookie(time() + 60)->getJson('Albums'));
		}

		Hash::shouldNotHaveReceived('check');
	}

	// ── RSS and embeds disabled (S-074-11) ───────────────────────

	public function testRssAndEmbedsDisabledWhilePasswordSet(): void
	{
		Configs::set('rss_enable', '1');
		Configs::set('is_embed_enabled', '1');
		$this->setGalleryPassword();

		$this->assertStatus($this->withUnlockCookie(time() + 60)->get('/feed'), 412);
		$this->assertStatus($this->actingAs($this->admin)->get('/feed'), 412);
		Auth::logout();
		Session::flush();
		$this->assertNotFound($this->withUnlockCookie(time() + 60)->getJson('Embed/' . $this->album4->id));
		$this->assertNotFound($this->getJson('Embed/stream'));

		$init = $this->withUnlockCookie(time() + 60)->getJson('Gallery::Init');
		$this->assertOk($init);
		$init->assertJson(['is_embed_enabled' => false]);
		self::assertStringNotContainsString('application/rss+xml', $this->withUnlockCookie(time() + 60)->get('/gallery')->getContent());
	}

	public function testRssAndEmbedsWorkWithoutPassword(): void
	{
		Configs::set('rss_enable', '1');
		Configs::set('is_embed_enabled', '1');

		$this->assertOk($this->get('/feed'));
		$this->assertOk($this->getJson('Embed/' . $this->album4->id));
	}

	// ── Unlock endpoint and Init flag (S-074-03..06, 09, 10, 17, 20) ──

	public function testInitReportsLockState(): void
	{
		$this->setGalleryPassword();

		$this->getJson('Gallery::Init')->assertJson(['is_gallery_locked' => true]);
		$this->withUnlockCookie(time() + 60)->getJson('Gallery::Init')->assertJson(['is_gallery_locked' => false]);
		$this->actingAs($this->userMayUpload1)->getJson('Gallery::Init')->assertJson(['is_gallery_locked' => false]);
	}

	public function testUnlockWithCorrectPassword(): void
	{
		$this->setGalleryPassword();

		$response = $this->postJson('Gallery::unlock', ['password' => self::PASSWORD]);
		$this->assertNoContent($response);
		$response->assertCookie(GalleryLockState::COOKIE_NAME);
		$cookie_value = $response->getCookie(GalleryLockState::COOKIE_NAME)->getValue();

		$this->assertOk($this->withCookie(GalleryLockState::COOKIE_NAME, $cookie_value)->getJson('Albums'));
		$this->withCookie(GalleryLockState::COOKIE_NAME, $cookie_value)->getJson('Gallery::Init')->assertJson(['is_gallery_locked' => false]);
	}

	public function testUnlockWithWrongPassword(): void
	{
		$this->setGalleryPassword();

		$response = $this->postJson('Gallery::unlock', ['password' => 'wrong']);
		$this->assertForbidden($response);
		$response->assertJson(['message' => 'Password is invalid']);
		$response->assertCookieMissing(GalleryLockState::COOKIE_NAME);
		$this->assertGalleryLocked($this->getJson('Albums'));
	}

	public function testUnlockRequiresPassword(): void
	{
		$this->setGalleryPassword();

		$this->assertUnprocessable($this->postJson('Gallery::unlock', []));
	}

	public function testUnlockIsNoOpWhenNoPasswordIsSet(): void
	{
		$response = $this->postJson('Gallery::unlock', ['password' => 'anything']);
		$this->assertNoContent($response);
		$response->assertCookieMissing(GalleryLockState::COOKIE_NAME);
	}

	public function testUnlockIsThrottled(): void
	{
		$this->setGalleryPassword();

		for ($i = 0; $i < 10; $i++) {
			$this->postJson('Gallery::unlock', ['password' => 'wrong']);
		}
		$this->assertStatus($this->postJson('Gallery::unlock', ['password' => 'wrong']), 429);
	}

	public function testChangingPasswordRelocks(): void
	{
		$this->setGalleryPassword();
		$this->assertOk($this->withUnlockCookie(time() + 60)->getJson('Albums'));

		Configs::set('gallery_password', Hash::make('another password'));

		$this->assertGalleryLocked($this->withUnlockCookie(time() + 60)->getJson('Albums'));
	}

	public function testClearingPasswordOpensGallery(): void
	{
		$this->setGalleryPassword();
		Configs::set('gallery_password', '');

		$this->assertOk($this->getJson('Albums'));
	}

	public function testUnlockSurvivesSessionFlush(): void
	{
		$this->setGalleryPassword();
		$this->withUnlockCookie(time() + 60);
		Session::flush();

		$this->assertOk($this->getJson('Albums'));
	}

	public function testCookieLifetimeFollowsConfig(): void
	{
		$this->setGalleryPassword();

		Configs::set('gallery_password_cookie_lifetime', '30');
		$cookie = $this->postJson('Gallery::unlock', ['password' => self::PASSWORD])->getCookie(GalleryLockState::COOKIE_NAME, false);
		self::assertNotNull($cookie);
		self::assertEqualsWithDelta(time() + 30 * 86400, $cookie->getExpiresTime(), 60);

		Configs::set('gallery_password_cookie_lifetime', '0');
		$cookie = $this->postJson('Gallery::unlock', ['password' => self::PASSWORD])->getCookie(GalleryLockState::COOKIE_NAME, false);
		self::assertNotNull($cookie);
		self::assertSame(0, $cookie->getExpiresTime());
	}
}
