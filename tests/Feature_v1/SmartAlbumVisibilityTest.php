<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v1;

use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Feature_v1\LibUnitTests\AlbumsUnitTest;
use Tests\Feature_v1\LibUnitTests\PhotosUnitTest;
use Tests\Feature_v1\LibUnitTests\RootAlbumUnitTest;
use Tests\Feature_v1\LibUnitTests\SharingUnitTest;
use Tests\Feature_v1\LibUnitTests\UsersUnitTest;
use Tests\Traits\InteractWithSmartAlbums;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyPhotos;
use Tests\Traits\RequiresEmptyUsers;

class SmartAlbumVisibilityTest extends AbstractTestCase
{
	use InteractWithSmartAlbums;
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use RequiresEmptyPhotos;

	protected AlbumsUnitTest $albums_tests;
	protected RootAlbumUnitTest $root_album_tests;
	protected UsersUnitTest $users_tests;
	protected SharingUnitTest $sharing_tests;
	protected PhotosUnitTest $photos_tests;

	/** @var bool the previously configured public visibility for "Recent" */
	private bool $isRecentAlbumPublic;

	/** @var bool the previously configured public visibility for "Starred" */
	private bool $isStarredAlbumPublic;

	/** @var bool the previously configured public visibility for "On This Day" */
	private bool $isOnThisDayAlbumPublic;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();
		$this->albums_tests = new AlbumsUnitTest($this);
		$this->root_album_tests = new RootAlbumUnitTest($this);
		$this->users_tests = new UsersUnitTest($this);
		$this->sharing_tests = new SharingUnitTest($this);
		$this->photos_tests = new PhotosUnitTest($this);

		$this->isRecentAlbumPublic = RecentAlbum::getInstance()->public_permissions() !== null;
		RecentAlbum::getInstance()->setPrivate();
		$this->isStarredAlbumPublic = StarredAlbum::getInstance()->public_permissions() !== null;
		StarredAlbum::getInstance()->setPrivate();
		$this->isOnThisDayAlbumPublic = OnThisDayAlbum::getInstance()->public_permissions() !== null;
		OnThisDayAlbum::getInstance()->setPrivate();
		$this->clearCachedSmartAlbums();
	}

	public function tearDown(): void
	{
		if ($this->isRecentAlbumPublic) {
			RecentAlbum::getInstance()->setPublic();
		} else {
			RecentAlbum::getInstance()->setPrivate();
		}

		if ($this->isStarredAlbumPublic) {
			StarredAlbum::getInstance()->setPublic();
		} else {
			StarredAlbum::getInstance()->setPrivate();
		}

		if ($this->isOnThisDayAlbumPublic) {
			OnThisDayAlbum::getInstance()->setPublic();
		} else {
			OnThisDayAlbum::getInstance()->setPrivate();
		}
		$this->clearCachedSmartAlbums();

		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	private function send(
		string $url,
		array $params,
		int $status = 204,
		?string $assertSee = null): void
	{
		Auth::loginUsingId(1);

		$response = $this->postJson('/api' . $url, $params);
		$this->assertStatus($response, $status);
		if ($assertSee !== null) {
			$response->assertSee($assertSee);
		}

		Auth::logout();
		Session::flush();
	}

	/**
	 * Start wih nothing
	 * toggle public one smart album after the other
	 * toggle private one smart album after the other.
	 *
	 * @return void
	 */
	public function testSettingsToPublicWorks(): void
	{
		$response = $this->root_album_tests->get();
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		], true);
		$response->assertDontSee('recent');
		$response->assertDontSee('starred');
		$response->assertDontSee('on_this_day');

		$this->send('/Settings::setSmartAlbumVisibility', ['albumID' => 'recent', 'is_public' => true]);
		$this->clearCachedSmartAlbums();
		$response = $this->root_album_tests->get();
		$response->assertJson([
			'smart_albums' => [
				'recent' => [],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		], true);

		$this->send('/Settings::setSmartAlbumVisibility', ['albumID' => 'starred', 'is_public' => true]);
		$this->clearCachedSmartAlbums();
		$response = $this->root_album_tests->get();
		$response->assertJson([
			'smart_albums' => [
				'recent' => [],
				'starred' => [],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		], true);

		$this->send('/Settings::setSmartAlbumVisibility', ['albumID' => 'on_this_day', 'is_public' => true]);
		$this->clearCachedSmartAlbums();
		$response = $this->root_album_tests->get();
		$response->assertJson([
			'smart_albums' => [
				'recent' => [],
				'starred' => [],
				'on_this_day' => [],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		], true);

		$this->send('/Settings::setSmartAlbumVisibility', ['albumID' => 'recent', 'is_public' => false]);
		$this->clearCachedSmartAlbums();
		$response = $this->root_album_tests->get();
		$response->assertJson([
			'smart_albums' => [
				'starred' => [],
				'on_this_day' => [],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		], true);

		$this->send('/Settings::setSmartAlbumVisibility', ['albumID' => 'starred', 'is_public' => false]);
		$this->clearCachedSmartAlbums();
		$response = $this->root_album_tests->get();
		$response->assertJson([
			'smart_albums' => [
				'on_this_day' => [],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		], true);

		$this->send('/Settings::setSmartAlbumVisibility', ['albumID' => 'on_this_day', 'is_public' => false]);
		$this->clearCachedSmartAlbums();
		$response = $this->root_album_tests->get();
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		], true);

		$this->send('/Settings::setSmartAlbumVisibility', ['albumID' => 'wrong', 'is_public' => false], 422);
		$this->send('/Settings::setSmartAlbumVisibility', ['albumID' => 'on_this_day', 'is_public' => 'true'], 422);
	}
}