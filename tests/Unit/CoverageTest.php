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

namespace Tests\Unit;

use App\DTO\BacktraceRecord;
use App\DTO\ImportEventReport;
use App\DTO\ImportProgressReport;
use App\Enum\AspectRatioCSSType;
use App\Enum\AspectRatioType;
use App\Enum\MapProviders;
use App\Enum\SmartAlbumType;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\SmartAlbums\UnsortedAlbum;
use Tests\AbstractTestCase;

class CoverageTest extends AbstractTestCase
{
	public function testBackEnumStuff(): void
	{
		self::assertEquals(['UNSORTED',
			'STARRED',
			'RECENT',
			'ON_THIS_DAY', ], SmartAlbumType::names());
		self::assertEquals([
			'UNSORTED' => 'unsorted',
			'STARRED' => 'starred',
			'RECENT' => 'recent',
			'ON_THIS_DAY' => 'on_this_day',
		], SmartAlbumType::array());
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
		self::assertEquals(['file' => 'file', 'line' => 1, 'method' => 'class::function'], $record->toArray());
	}

	public function testImportProgressReport(): void
	{
		$report = ImportProgressReport::create(
			path: 'path',
			progress: 1,
		);
		self::assertEquals('path: 1%', $report->toCLIString());
	}

	public function testImportEventReport(): void
	{
		$report = ImportEventReport::createWarning(
			subtype: 'subtype',
			path: 'path',
			message: 'message',
		);
		self::assertEquals('path: message', $report->toCLIString());

		$report = ImportEventReport::createWarning(
			subtype: 'subtype',
			path: null,
			message: 'message',
		);
		self::assertEquals('message', $report->toCLIString());
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
}
