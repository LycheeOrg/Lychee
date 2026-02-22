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

namespace Tests\Unit;

use App\Actions\User\Create;
use App\Assets\ArrayToTextTable;
use App\DTO\BacktraceRecord;
use App\DTO\ImportEventReport;
use App\Enum\AspectRatioCSSType;
use App\Enum\AspectRatioType;
use App\Enum\JobStatus;
use App\Enum\MapProviders;
use App\Enum\SmartAlbumType;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Factories\AlbumFactory;
use App\Image\Files\ProcessableJobFile;
use App\Jobs\ProcessImageJob;
use App\Models\Album;
use App\Models\User;
use App\Relations\HasAlbumThumb;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Tests\AbstractTestCase;

class CoverageTest extends AbstractTestCase
{
	public function testBackEnumStuff(): void
	{
		self::assertEquals(['UNSORTED',
			'HIGHLIGHTED',
			'RECENT',
			'ON_THIS_DAY',
			'UNTAGGED',
			'UNRATED',
			'ONE_STAR',
			'TWO_STARS',
			'THREE_STARS',
			'FOUR_STARS',
			'FIVE_STARS',
			'BEST_PICTURES',
			'MY_RATED_PICTURES',
			'MY_BEST_PICTURES',
		], SmartAlbumType::names());
		self::assertEquals([
			'UNSORTED' => 'unsorted',
			'HIGHLIGHTED' => 'highlighted',
			'RECENT' => 'recent',
			'ON_THIS_DAY' => 'on_this_day',
			'UNTAGGED' => 'untagged',
			'UNRATED' => 'unrated',
			'ONE_STAR' => 'one_star',
			'TWO_STARS' => 'two_stars',
			'THREE_STARS' => 'three_stars',
			'FOUR_STARS' => 'four_stars',
			'FIVE_STARS' => 'five_stars',
			'BEST_PICTURES' => 'best_pictures',
			'MY_RATED_PICTURES' => 'my_rated_pictures',
			'MY_BEST_PICTURES' => 'my_best_pictures',
		], SmartAlbumType::array());

		self::assertEquals('failure', JobStatus::FAILURE->name());
	}

	public function testMapProvidersEnum(): void
	{
		self::assertEquals('https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png', MapProviders::Wikimedia->getLayer());
		self::assertEquals('https://tile.openstreetmap.org/{z}/{x}/{y}.png', MapProviders::OpenStreetMapOrg->getLayer());
		self::assertEquals('https://tile.openstreetmap.de/{z}/{x}/{y}.png ', MapProviders::OpenStreetMapDe->getLayer());
		self::assertEquals('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png ', MapProviders::OpenStreetMapFr->getLayer());
		self::assertEquals('https://{s}.osm.rrze.fau.de/osmhd/{z}/{x}/{y}.png', MapProviders::RRZE->getLayer());

		self::assertEquals('<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>', MapProviders::Wikimedia->getAtributionHtml());
		self::assertEquals('&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>', MapProviders::OpenStreetMapOrg->getAtributionHtml());
		self::assertEquals('&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>', MapProviders::OpenStreetMapDe->getAtributionHtml());
		self::assertEquals('&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>', MapProviders::OpenStreetMapFr->getAtributionHtml());
		self::assertEquals('&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>', MapProviders::RRZE->getAtributionHtml());

		self::assertEquals(AspectRatioCSSType::aspect2by3, AspectRatioType::aspect2by3->css());
	}

	public function testBackTraceReccord(): void
	{
		$record = new BacktraceRecord(
			file: 'file',
			line: 1,
			class: 'class',
			function: 'function',
		);

		self::assertEquals('file', $record->getFile());
		self::assertEquals('function', $record->getFunction());
		self::assertEquals('class', $record->getClass());
	}

	public function testImportEventReport(): void
	{
		$report = ImportEventReport::createWarning(
			subtype: 'subtype',
			path: 'path',
			message: 'message',
		);
		self::assertEquals('<comment>path: message</comment>', $report->toCLIString());

		$report = ImportEventReport::createWarning(
			subtype: 'subtype',
			path: null,
			message: 'message',
		);
		self::assertEquals('<comment>message</comment>', $report->toCLIString());
	}

	public function testBaseSmartAlbumException(): void
	{
		self::expectException(LycheeInvalidArgumentException::class);

		$album = new UnsortedAlbum();
		$album->__get('');
	}

	public function testBaseSmartAlbumException2(): void
	{
		self::expectException(LycheeInvalidArgumentException::class);

		$album = new UnsortedAlbum();
		$album->__get('something');
	}

	public function testBaseSmartAlbumPhotos(): void
	{
		$album = new UnsortedAlbum();
		$data = $album->__get('Photos');
		self::assertEmpty($data);

		$data = $album->getPhotos();
		self::assertEmpty($data);

		$album->setPublic();
		$album->setPublic();
		$data = $album->getPhotos();
		self::assertEmpty($data);
		$album->setPrivate();
		$album->setPrivate();
	}

	public function testArrayToText(): void
	{
		$array = new ArrayToTextTable([]);
		self::assertEquals("┌┐\n└┘\n", $array->__toString());

		// test the other methods.
		$array->setData(null);
		$array->setData([['a', 'b', 'c']]);
		$array->setFormatter(fn ($value) => $value);
		self::assertEquals("┌───┬───┬───┐\n│ a │ b │ c │\n└───┴───┴───┘\n", $array->getTable());
	}

	public function testAlbumFactory(): void
	{
		$factory = resolve(AlbumFactory::class);
		self::assertCount(1, $factory->findAbstractAlbumsOrFail([UnsortedAlbum::ID], false));

		self::expectException(ModelNotFoundException::class);
		$factory->findBaseAlbumsOrFail([UnsortedAlbum::ID], false);
	}

	public function testJobFailing(): void
	{
		$userCreate = resolve(Create::class);
		$user = $userCreate->do(
			'username',
			'password',
			'email',
			true,
			true,
			false,
			0,
			'note'
		);

		Auth::login($user);
		$file = new ProcessableJobFile('.jpg', 'something');
		$job = new ProcessImageJob(
			$file,
			UnsortedAlbum::ID,
			null
		);
		$job->failed(new LycheeInvalidArgumentException('something'));
		$job->failed(new \Exception('something', 999));

		Auth::logout();
		$user->delete();
		self::assertTrue(true);
	}

	public function testHasAlbumThumbCoverTypeForExplicitCover(): void
	{
		// Mock album with explicit cover_id
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->cover_id = 'explicit-cover-id';
		$album->auto_cover_id_max_privilege = 'max-priv-id';
		$album->auto_cover_id_least_privilege = 'least-priv-id';

		$relation = new HasAlbumThumb($album);
		$method = new \ReflectionMethod(HasAlbumThumb::class, 'getCoverTypeForAlbum');

		$cover_type = $method->invoke($relation, $album);
		self::assertEquals('cover_id', $cover_type);
	}

	public function testHasAlbumThumbCoverTypeForAdminUser(): void
	{
		// Mock admin user
		$admin = \Mockery::mock(User::class)->makePartial();
		$admin->id = 1;
		$admin->may_administrate = true;

		Auth::shouldReceive('user')->andReturn($admin);

		// Mock album without explicit cover
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->cover_id = null;
		$album->owner_id = 999;
		$album->auto_cover_id_max_privilege = 'max-priv-id';
		$album->auto_cover_id_least_privilege = 'least-priv-id';

		$relation = new HasAlbumThumb($album);
		$method = new \ReflectionMethod(HasAlbumThumb::class, 'getCoverTypeForAlbum');

		$cover_type = $method->invoke($relation, $album);
		self::assertEquals('auto_cover_id_max_privilege', $cover_type);
	}

	public function testHasAlbumThumbCoverTypeForOwner(): void
	{
		// Mock owner user
		$owner = \Mockery::mock(User::class)->makePartial();
		$owner->id = 42;
		$owner->may_administrate = false;

		Auth::shouldReceive('user')->andReturn($owner);

		// Mock album owned by user
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->cover_id = null;
		$album->owner_id = 42;
		$album->auto_cover_id_max_privilege = 'max-priv-id';
		$album->auto_cover_id_least_privilege = 'least-priv-id';

		$relation = new HasAlbumThumb($album);
		$method = new \ReflectionMethod(HasAlbumThumb::class, 'getCoverTypeForAlbum');

		$cover_type = $method->invoke($relation, $album);
		self::assertEquals('auto_cover_id_max_privilege', $cover_type);
	}

	public function testHasAlbumThumbCoverTypeForPublicUser(): void
	{
		// Mock no authenticated user (public view)
		Auth::shouldReceive('user')->andReturn(null);

		// Mock album without explicit cover
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->cover_id = null;
		$album->owner_id = 999;
		$album->auto_cover_id_max_privilege = 'max-priv-id';
		$album->auto_cover_id_least_privilege = 'least-priv-id';

		$relation = new HasAlbumThumb($album);
		$method = new \ReflectionMethod(HasAlbumThumb::class, 'getCoverTypeForAlbum');

		$cover_type = $method->invoke($relation, $album);
		self::assertEquals('auto_cover_id_least_privilege', $cover_type);
	}

	public function testHasAlbumThumbSelectCoverIdWithExplicitCover(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->cover_id = 'explicit-cover-id';
		$album->auto_cover_id_max_privilege = 'max-priv-id';
		$album->auto_cover_id_least_privilege = 'least-priv-id';

		$relation = new HasAlbumThumb($album);
		$method = new \ReflectionMethod(HasAlbumThumb::class, 'selectCoverIdForAlbum');

		$selected_id = $method->invoke($relation, $album);
		self::assertEquals('explicit-cover-id', $selected_id);
	}

	public function testHasAlbumThumbSelectCoverIdWithMaxPrivilege(): void
	{
		// Mock admin user
		$admin = \Mockery::mock(User::class)->makePartial();
		$admin->id = 1;
		$admin->may_administrate = true;

		Auth::shouldReceive('user')->andReturn($admin);

		$album = \Mockery::mock(Album::class)->makePartial();
		$album->cover_id = null;
		$album->owner_id = 999;
		$album->auto_cover_id_max_privilege = 'max-priv-id';
		$album->auto_cover_id_least_privilege = 'least-priv-id';

		$relation = new HasAlbumThumb($album);
		$method = new \ReflectionMethod(HasAlbumThumb::class, 'selectCoverIdForAlbum');

		$selected_id = $method->invoke($relation, $album);
		self::assertEquals('max-priv-id', $selected_id);
	}

	public function testHasAlbumThumbSelectCoverIdWithLeastPrivilege(): void
	{
		// Mock no authenticated user (public view)
		Auth::shouldReceive('user')->andReturn(null);

		$album = \Mockery::mock(Album::class)->makePartial();
		$album->cover_id = null;
		$album->owner_id = 999;
		$album->auto_cover_id_max_privilege = 'max-priv-id';
		$album->auto_cover_id_least_privilege = 'least-priv-id';

		$relation = new HasAlbumThumb($album);
		$method = new \ReflectionMethod(HasAlbumThumb::class, 'selectCoverIdForAlbum');

		$selected_id = $method->invoke($relation, $album);
		self::assertEquals('least-priv-id', $selected_id);
	}

	public function testHasAlbumThumbSelectCoverIdReturnsNull(): void
	{
		// Mock no authenticated user (public view)
		Auth::shouldReceive('user')->andReturn(null);

		// Test the case where no cover IDs are available
		// We can't instantiate HasAlbumThumb when all covers are null because
		// it triggers the fallback query in addConstraints() which requires DB.
		// Instead, we test the logic by verifying getCoverTypeForAlbum returns
		// the expected type and that selectCoverIdForAlbum would return null.

		$album = \Mockery::mock(Album::class)->makePartial();
		$album->cover_id = null;
		$album->auto_cover_id_max_privilege = null;
		$album->auto_cover_id_least_privilege = null;
		$album->is_nsfw = false;
		$album->shouldReceive('getAttribute')
			->with('owner_id')
			->andReturn(999);

		// Use a different album with non-null cover for the parent so
		// HasAlbumThumb can be instantiated without triggering DB query
		$parentAlbum = \Mockery::mock(Album::class)->makePartial();
		$parentAlbum->cover_id = 'some-cover-id';  // Non-null to avoid fallback
		$parentAlbum->is_nsfw = false;

		$relation = new HasAlbumThumb($parentAlbum);
		$method = new \ReflectionMethod(HasAlbumThumb::class, 'selectCoverIdForAlbum');

		// Test with the album that has null covers
		$selected_id = $method->invoke($relation, $album);
		self::assertNull($selected_id);
	}
}
