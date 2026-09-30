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
use App\Models\AlbumUserThumb;
use App\Models\User;
use App\Relations\HasAlbumThumb;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
			'TIMELINE',
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
			'TIMELINE' => 'timeline',
		], SmartAlbumType::array());

		self::assertEquals('failure', JobStatus::FAILURE->name());
	}

	public function testMapProvidersEnum(): void
	{
		self::assertEquals('https://tile.openstreetmap.org/{z}/{x}/{y}.png', MapProviders::OpenStreetMapOrg->getLayer());
		self::assertEquals('https://tile.openstreetmap.de/{z}/{x}/{y}.png ', MapProviders::OpenStreetMapDe->getLayer());
		self::assertEquals('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png ', MapProviders::OpenStreetMapFr->getLayer());
		self::assertEquals('https://{s}.osm.rrze.fau.de/osmhd/{z}/{x}/{y}.png', MapProviders::RRZE->getLayer());

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

	/**
	 * An album mock carrying `$cover_id` and the given precomputed cover rows
	 * (Feature 076), owned by user 999.
	 *
	 * @param array<int,AlbumUserThumb> $rows
	 */
	private function albumWithCoverRows(?string $cover_id, array $rows): Album
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->cover_id = $cover_id;
		$album->owner_id = 999;
		$album->is_nsfw = false;
		$album->setRelation('autoCoverRows', new EloquentCollection($rows));

		return $album;
	}

	private function coverRow(?int $user_id, string $photo_id): AlbumUserThumb
	{
		return new AlbumUserThumb(['user_id' => $user_id, 'photo_id' => $photo_id, 'is_precomputed' => true]);
	}

	/**
	 * `selectCoverIdForAlbum()` invoked on `$album`, through a relation whose
	 * own parent has an explicit cover (so constructing it runs no query).
	 */
	private function selectedCoverId(Album $album): ?string
	{
		$relation = new HasAlbumThumb($this->albumWithCoverRows('some-cover-id', []));
		$method = new \ReflectionMethod(HasAlbumThumb::class, 'selectCoverIdForAlbum');

		return $method->invoke($relation, $album);
	}

	private function loginAs(int $id, bool $is_admin): void
	{
		$user = \Mockery::mock(User::class)->makePartial();
		$user->id = $id;
		$user->may_administrate = $is_admin;
		Auth::shouldReceive('user')->andReturn($user);
	}

	public function testHasAlbumThumbExplicitCoverWins(): void
	{
		Auth::shouldReceive('user')->andReturn(null);
		$album = $this->albumWithCoverRows('explicit-cover-id', [$this->coverRow(999, 'max-priv-id'), $this->coverRow(null, 'least-priv-id')]);

		self::assertEquals('explicit-cover-id', $this->selectedCoverId($album));
	}

	public function testHasAlbumThumbAdminGetsOwnerRow(): void
	{
		$this->loginAs(1, true);
		$album = $this->albumWithCoverRows(null, [$this->coverRow(999, 'max-priv-id'), $this->coverRow(null, 'least-priv-id')]);

		self::assertEquals('max-priv-id', $this->selectedCoverId($album));
	}

	public function testHasAlbumThumbOwnerGetsOwnerRow(): void
	{
		$this->loginAs(999, false);
		$album = $this->albumWithCoverRows(null, [$this->coverRow(999, 'max-priv-id'), $this->coverRow(null, 'least-priv-id')]);

		self::assertEquals('max-priv-id', $this->selectedCoverId($album));
	}

	public function testHasAlbumThumbGuestGetsPublicRow(): void
	{
		Auth::shouldReceive('user')->andReturn(null);
		$album = $this->albumWithCoverRows(null, [$this->coverRow(null, 'least-priv-id')]);

		self::assertEquals('least-priv-id', $this->selectedCoverId($album));
	}

	public function testHasAlbumThumbSelectCoverIdReturnsNull(): void
	{
		Auth::shouldReceive('user')->andReturn(null);
		$album = $this->albumWithCoverRows(null, []);

		self::assertNull($this->selectedCoverId($album));
	}
}
