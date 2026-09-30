<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Photo;

use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Http\Middleware\ReadOnlyStartSession;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\SortedMiddleware;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Image/asset routes (`GET /api/v3/Asset/...`, `GET /image/{path}`) read the
 * session (authenticated user, unlocked albums) but never write it back nor
 * issue cookies.
 */
class AssetMiddlewareV3Test extends BaseApiWithDataTest
{
	private const PREVIOUS_URL = 'https://lychee.test/gallery';

	public function setUp(): void
	{
		parent::setUp();
		Storage::fake(StorageDiskType::LOCAL->value);
	}

	public function tearDown(): void
	{
		Configs::set('temporary_image_link_enabled', '0');
		parent::tearDown();
	}

	private function putThumbBytes(Photo $photo): void
	{
		$variant = SizeVariant::query()
			->where('photo_id', '=', $photo->id)
			->where('type', '=', SizeVariantType::THUMB)
			->firstOrFail();
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, 'thumb-bytes');
	}

	private function assertNoCookies(TestResponse $response): void
	{
		self::assertSame([], $response->headers->getCookies());
	}

	public function testAssetSetsNoCookiesAndDoesNotTouchPreviousUrl(): void
	{
		$this->putThumbBytes($this->photo1);
		session()->setPreviousUrl(self::PREVIOUS_URL);

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/thumb");

		$response->assertOk();
		$this->assertNoCookies($response);
		self::assertSame(self::PREVIOUS_URL, session()->previousUrl());
	}

	/**
	 * The unlocked album ids are only known through the session cookie: the
	 * route must still open the session it names.
	 */
	public function testAssetOfUnlockedAlbumIsServedFromSessionCookie(): void
	{
		$locked_album = Album::factory()->as_root()->owned_by($this->userLocked)->create();
		AccessPermission::factory()->public()->visible()->locked()->for_album($locked_album)->create();
		$photo = Photo::factory()->owned_by($this->userLocked)->in($locked_album)->create();
		$this->putThumbBytes($photo);
		$uri = "Asset/{$locked_album->id}/{$photo->id}/thumb";

		$session_id = Str::random(40);
		session()->getHandler()->write($session_id, serialize([AlbumPolicy::UNLOCKED_ALBUMS_SESSION_KEY => [$locked_album->id]]));

		$this->assertForbidden($this->getV3($uri));

		$response = $this->withCookie(config('session.cookie'), $session_id)->getV3($uri);
		$response->assertOk();
		self::assertSame('thumb-bytes', $response->streamedContent());
	}

	public function testImageSetsNoCookiesAndDoesNotTouchPreviousUrl(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$url = $this->getJsonWithData('Album::photos', ['album_id' => $this->album4->id])->json('photos.0.size_variants.medium.url');
		self::assertStringContainsString('/image/medium/', $url);
		session()->setPreviousUrl(self::PREVIOUS_URL);

		$response = $this->get($url);

		// The file itself is not on the fake disk: the request went through
		// the whole stack and was authorized.
		$this->assertNotFound($response);
		$this->assertNoCookies($response);
		self::assertSame(self::PREVIOUS_URL, session()->previousUrl());
	}

	/**
	 * @return string[] the route's middleware, resolved and in execution order
	 */
	private function resolvedMiddleware(string $uri): array
	{
		$route = Route::getRoutes()->match(Request::create($uri));
		$middleware = Route::gatherRouteMiddleware($route);

		return (new SortedMiddleware(app(Kernel::class)->getMiddlewarePriority(), $middleware))->all();
	}

	/**
	 * @return string[][]
	 */
	public static function assetRoutes(): array
	{
		return [
			'asset' => ['/api/v3/Asset/album/photo/thumb'],
			'image' => ['/image/medium/foo.jpg'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('assetRoutes')]
	public function testRouteReadsSessionBeforeAuthenticatingItAndNeverWritesIt(string $uri): void
	{
		$middleware = $this->resolvedMiddleware($uri);

		self::assertNotContains(StartSession::class, $middleware);
		self::assertNotContains(VerifyCsrfToken::class, $middleware);
		$read_only = array_search(ReadOnlyStartSession::class, $middleware, true);
		$authenticate = array_search(AuthenticateSession::class, $middleware, true);
		self::assertNotFalse($read_only);
		self::assertNotFalse($authenticate);
		self::assertLessThan($authenticate, $read_only);
	}
}
