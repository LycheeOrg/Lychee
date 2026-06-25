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

namespace Tests\Unit\View\Components;

use App\Constants\FileSystem;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\OgImageAlbumSourceType;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Http\Controllers\Gallery\AlbumController;
use App\Image\Watermarker;
use App\Models\Album;
use App\Models\Extensions\SizeVariants;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use App\Services\UrlGenerator;
use App\View\Components\Meta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class MetaTest extends AbstractTestCase
{
	private MockInterface&ConfigManager $config_manager;

	protected function setUp(): void
	{
		parent::setUp();
		$this->withoutVite();

		$this->config_manager = \Mockery::mock(ConfigManager::class);
		request()->attributes->set('configs', $this->config_manager);

		$this->setUpDefaultConfig();
		Storage::fake(FileSystem::DIST);

		config(['app.dir_url' => '']);
	}

	private function setUpDefaultConfig(): void
	{
		$this->config_manager->shouldReceive('getValueAsString')
			->with('site_owner')->andReturn('Test Owner')->byDefault();
		$this->config_manager->shouldReceive('getValueAsBool')
			->with('rss_enable')->andReturn(false)->byDefault();
		$this->config_manager->shouldReceive('getValueAsString')
			->with('site_title')->andReturn('My Gallery')->byDefault();
		$this->config_manager->shouldReceive('getValueAsString')
			->with('sm_card_image_url')->andReturn('https://example.com/card.jpg')->byDefault();
		$this->config_manager->shouldReceive('getValueAsString')
			->with('landing_background_landscape')->andReturn('https://example.com/bg.jpg')->byDefault();
		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::HEADER)->byDefault();
		$this->config_manager->shouldReceive('getValueAsBool')
			->with('use_album_compact_header')->andReturn(true)->byDefault();
	}

	private function buildMeta(): Meta
	{
		return new Meta();
	}

	private function makeSizeVariantMock(string $url): MockInterface&SizeVariant
	{
		$sv = \Mockery::mock(SizeVariant::class);
		$sv->shouldReceive('offsetExists')->with('url')->andReturn(true);
		$sv->shouldReceive('getAttribute')->with('url')->andReturn($url);

		return $sv;
	}

	private function makePhotoMock(string $title, ?string $description, MockInterface $size_variants): MockInterface&Photo
	{
		$photo = \Mockery::mock(Photo::class)->makePartial();
		$photo->forceFill(['title' => $title, 'description' => $description]);
		$photo->setRelation('size_variants', $size_variants);

		return $photo;
	}

	public function testDefaultConstruction(): void
	{
		$meta = $this->buildMeta();

		self::assertSame('Test Owner', $meta->site_owner);
		self::assertSame('My Gallery', $meta->page_title);
		self::assertSame('', $meta->page_description);
		self::assertSame('https://example.com/card.jpg', $meta->image_url);
		self::assertFalse($meta->rss_enable);
	}

	public function testRssEnabled(): void
	{
		$this->config_manager->shouldReceive('getValueAsBool')
			->with('rss_enable')->andReturn(true);

		$meta = $this->buildMeta();

		self::assertTrue($meta->rss_enable);
	}

	public function testSmCardImageUrlFallsBackToLandingBackground(): void
	{
		$this->config_manager->shouldReceive('getValueAsString')
			->with('sm_card_image_url')->andReturn('');

		$meta = $this->buildMeta();

		self::assertSame('https://example.com/bg.jpg', $meta->image_url);
	}

	public function testSmCardImageUrlWithAbsolutePath(): void
	{
		$this->config_manager->shouldReceive('getValueAsString')
			->with('sm_card_image_url')->andReturn('/images/card.png');

		$meta = $this->buildMeta();

		self::assertSame('/images/card.png', $meta->image_url);
	}

	public function testSmCardImageUrlWithFullUrl(): void
	{
		$this->config_manager->shouldReceive('getValueAsString')
			->with('sm_card_image_url')->andReturn('https://cdn.example.com/img.jpg');

		$meta = $this->buildMeta();

		self::assertSame('https://cdn.example.com/img.jpg', $meta->image_url);
	}

	public function testBaseUrlWithMatchingDirUrl(): void
	{
		$base = url('/');
		$suffix = '/lychee';
		config(['app.dir_url' => $suffix]);
		$this->app['url']->forceRootUrl($base . $suffix);

		$meta = $this->buildMeta();

		self::assertSame($base . $suffix, $meta->base_url);
	}

	public function testBaseUrlWithNonMatchingDirUrl(): void
	{
		config(['app.dir_url' => '/gallery']);

		$meta = $this->buildMeta();

		$expected = url('/gallery/');
		self::assertSame($expected, $meta->base_url);
	}

	public function testBaseUrlWithEmptyDirUrl(): void
	{
		config(['app.dir_url' => '']);

		$meta = $this->buildMeta();

		$base = url('/');
		self::assertSame($base, $meta->base_url);
	}

	public function testPageUrlIsCurrentUrl(): void
	{
		$meta = $this->buildMeta();

		self::assertSame(url()->current(), $meta->page_url);
	}

	public function testAccessDeniedPreventsAlbumAndPhotoProcessing(): void
	{
		$album = \Mockery::mock(Album::class);
		$album->shouldReceive('get_title')->never();

		session(['access' => false]);
		session(['album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('My Gallery', $meta->page_title);
		self::assertSame('', $meta->page_description);
	}

	public function testAlbumInSessionSetsTitle(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->shouldReceive('get_title')->andReturn('Vacation 2024');
		$album->shouldReceive('getAttribute')->with('description')->andReturn('Summer photos');
		$album->shouldReceive('getAttribute')->with('cover_id')->andReturn(null);
		$album->shouldReceive('getAttribute')->with('auto_cover_id_least_privilege')->andReturn(null);

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::COVER);

		session(['album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('Vacation 2024', $meta->page_title);
		self::assertSame('Summer photos', $meta->page_description);
	}

	public function testAlbumWithNullDescriptionFallsBackToSiteTitle(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->shouldReceive('get_title')->andReturn('Untitled');
		$album->shouldReceive('getAttribute')->with('description')->andReturn(null);
		$album->shouldReceive('getAttribute')->with('cover_id')->andReturn(null);
		$album->shouldReceive('getAttribute')->with('auto_cover_id_least_privilege')->andReturn(null);

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::COVER);

		session(['album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('Untitled', $meta->page_title);
		self::assertSame('My Gallery', $meta->page_description);
	}

	public function testNonBaseAlbumDoesNotSetDescription(): void
	{
		$album = \Mockery::mock(AbstractAlbum::class);
		$album->shouldReceive('get_title')->andReturn('Smart Album');
		$album->shouldReceive('get_photos')->andReturn(new Collection());

		session(['album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('Smart Album', $meta->page_title);
		self::assertSame('', $meta->page_description);
	}

	public function testPhotoInSessionSetsMetadata(): void
	{
		$svMock = $this->makeSizeVariantMock('https://example.com/medium.jpg');

		$sizeVariants = \Mockery::mock(SizeVariants::class);
		$sizeVariants->shouldReceive('getMedium')->andReturn($svMock);

		$photo = $this->makePhotoMock('Sunset', 'A beautiful sunset', $sizeVariants);

		session(['photo' => $photo]);

		$meta = $this->buildMeta();

		self::assertSame('Sunset', $meta->page_title);
		self::assertSame('A beautiful sunset', $meta->page_description);
		self::assertSame('https://example.com/medium.jpg', $meta->image_url);
	}

	public function testPhotoWithNullDescriptionFallsBackToSiteTitle(): void
	{
		$sizeVariants = \Mockery::mock(SizeVariants::class);
		$sizeVariants->shouldReceive('getMedium')->andReturn(null);
		$sizeVariants->shouldReceive('getSmall')->andReturn(null);

		$photo = $this->makePhotoMock('No Description', null, $sizeVariants);

		session(['photo' => $photo]);

		$meta = $this->buildMeta();

		self::assertSame('No Description', $meta->page_title);
		self::assertSame('My Gallery', $meta->page_description);
		self::assertSame('https://example.com/card.jpg', $meta->image_url);
	}

	public function testPhotoFallsBackToSmallWhenNoMedium(): void
	{
		$svSmall = $this->makeSizeVariantMock('https://example.com/small.jpg');

		$sizeVariants = \Mockery::mock(SizeVariants::class);
		$sizeVariants->shouldReceive('getMedium')->andReturn(null);
		$sizeVariants->shouldReceive('getSmall')->andReturn($svSmall);

		$photo = $this->makePhotoMock('Photo', 'Desc', $sizeVariants);

		session(['photo' => $photo]);

		$meta = $this->buildMeta();

		self::assertSame('https://example.com/small.jpg', $meta->image_url);
	}

	public function testPhotoFallsBackToDefaultWhenNoSizeVariants(): void
	{
		$sizeVariants = \Mockery::mock(SizeVariants::class);
		$sizeVariants->shouldReceive('getMedium')->andReturn(null);
		$sizeVariants->shouldReceive('getSmall')->andReturn(null);

		$photo = $this->makePhotoMock('Photo', 'Desc', $sizeVariants);

		session(['photo' => $photo]);

		$meta = $this->buildMeta();

		self::assertSame('https://example.com/card.jpg', $meta->image_url);
	}

	public function testPhotoOverridesAlbumWhenBothInSession(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->shouldReceive('get_title')->andReturn('Album Title');
		$album->shouldReceive('getAttribute')->with('description')->andReturn('Album Desc');
		$album->shouldReceive('getAttribute')->with('cover_id')->andReturn(null);
		$album->shouldReceive('getAttribute')->with('auto_cover_id_least_privilege')->andReturn(null);

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::COVER);

		$svMedium = $this->makeSizeVariantMock('https://example.com/photo-medium.jpg');

		$sizeVariants = \Mockery::mock(SizeVariants::class);
		$sizeVariants->shouldReceive('getMedium')->andReturn($svMedium);

		$photo = $this->makePhotoMock('Photo Title', 'Photo Desc', $sizeVariants);

		session(['album' => $album, 'photo' => $photo]);

		$meta = $this->buildMeta();

		self::assertSame('Photo Title', $meta->page_title);
		self::assertSame('Photo Desc', $meta->page_description);
		self::assertSame('https://example.com/photo-medium.jpg', $meta->image_url);
	}

	public function testSessionDataIsForgottenAfterConstruction(): void
	{
		$sizeVariants = \Mockery::mock(SizeVariants::class);
		$sizeVariants->shouldReceive('getMedium')->andReturn(null);
		$sizeVariants->shouldReceive('getSmall')->andReturn(null);

		$album = \Mockery::mock(AbstractAlbum::class);
		$album->shouldReceive('get_title')->andReturn('T');
		$album->shouldReceive('get_photos')->andReturn(new Collection());

		$photo = $this->makePhotoMock('P', 'D', $sizeVariants);

		session(['access' => true, 'album' => $album, 'photo' => $photo]);

		$this->buildMeta();

		self::assertFalse(session()->has('access'));
		self::assertFalse(session()->has('album'));
		self::assertFalse(session()->has('photo'));
	}

	public function testGetUserCustomFilesWithExistingFile(): void
	{
		Storage::disk(FileSystem::DIST)->put('user.css', 'body{}');

		$url = Meta::getUserCustomFiles('user.css');

		self::assertStringContainsString('user.css', $url);
		self::assertStringContainsString('?', $url);
	}

	public function testGetUserCustomFilesWithNonExistingFile(): void
	{
		$url = Meta::getUserCustomFiles('nonexistent.css');

		self::assertStringContainsString('nonexistent.css', $url);
		self::assertStringNotContainsString('?', $url);
	}

	public function testGetUserCustomFilesCustomJs(): void
	{
		Storage::disk(FileSystem::DIST)->put('custom.js', 'alert(1)');

		$url = Meta::getUserCustomFiles('custom.js');

		self::assertStringContainsString('custom.js', $url);
		self::assertStringContainsString('?', $url);
	}

	public function testAlbumWithCoverSourceAndNonAlbumTypeReturnsNull(): void
	{
		$album = \Mockery::mock(AbstractAlbum::class);
		$album->shouldReceive('get_title')->andReturn('Smart Album');
		$album->shouldReceive('get_photos')->andReturn(new Collection());

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::COVER);

		session(['album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('https://example.com/card.jpg', $meta->image_url);
	}

	public function testAlbumWithCoverSourceAndNullCoverIds(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->shouldReceive('get_title')->andReturn('Album');
		$album->shouldReceive('getAttribute')->with('description')->andReturn(null);
		$album->shouldReceive('getAttribute')->with('cover_id')->andReturn(null);
		$album->shouldReceive('getAttribute')->with('auto_cover_id_least_privilege')->andReturn(null);

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::COVER);

		session(['album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('https://example.com/card.jpg', $meta->image_url);
	}

	public function testAlbumWithHeaderSourceAndCompactHeaderEnabled(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->shouldReceive('get_title')->andReturn('Album');
		$album->shouldReceive('getAttribute')->with('description')->andReturn('Desc');

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::HEADER);

		$this->config_manager->shouldReceive('getValueAsBool')
			->with('use_album_compact_header')->andReturn(true);

		session(['album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('https://example.com/card.jpg', $meta->image_url);
	}

	public function testAlbumWithHeaderSourceAndCompactHeaderId(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->shouldReceive('get_title')->andReturn('Album');
		$album->shouldReceive('getAttribute')->with('description')->andReturn('Desc');
		$album->shouldReceive('getAttribute')->with('header_id')->andReturn(AlbumController::COMPACT_HEADER);
		$album->shouldReceive('get_photos')->andReturn(new Collection());

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::HEADER);

		$this->config_manager->shouldReceive('getValueAsBool')
			->with('use_album_compact_header')->andReturn(false);

		session(['album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('https://example.com/card.jpg', $meta->image_url);
	}

	public function testAlbumWithHeaderSourceAndEmptyPhotos(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->shouldReceive('get_title')->andReturn('Empty Album');
		$album->shouldReceive('getAttribute')->with('description')->andReturn('No photos');
		$album->shouldReceive('getAttribute')->with('header_id')->andReturn(null);
		$album->shouldReceive('get_photos')->andReturn(new Collection());

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::HEADER);

		$this->config_manager->shouldReceive('getValueAsBool')
			->with('use_album_compact_header')->andReturn(false);

		session(['album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('https://example.com/card.jpg', $meta->image_url);
	}

	public function testAlbumWithHeaderSourceSetsImageUrl(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->shouldReceive('get_title')->andReturn('Album');
		$album->shouldReceive('getAttribute')->with('description')->andReturn('Desc');

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::HEADER);

		session(['album' => $album]);

		$meta = \Mockery::mock(Meta::class)
			->makePartial()
			->shouldAllowMockingProtectedMethods();
		$meta->shouldReceive('getHeaderUrl')
			->once()
			->andReturn('https://example.com/header.jpg');
		$meta->__construct();

		self::assertSame('https://example.com/header.jpg', $meta->image_url);
		self::assertSame('Album', $meta->page_title);
	}

	public function testAlbumWithCoverSourceSetsImageUrl(): void
	{
		$album = \Mockery::mock(Album::class)->makePartial();
		$album->shouldReceive('get_title')->andReturn('Album');
		$album->shouldReceive('getAttribute')->with('description')->andReturn('Desc');
		$album->shouldReceive('getAttribute')->with('cover_id')->andReturn('photo-123');
		$album->shouldReceive('getAttribute')->with('auto_cover_id_least_privilege')->andReturn(null);

		$this->config_manager->shouldReceive('getValueAsEnum')
			->with('sm_card_album_source', OgImageAlbumSourceType::class)
			->andReturn(OgImageAlbumSourceType::COVER);

		session(['album' => $album]);

		$mockStatement = \Mockery::mock(\PDOStatement::class);
		$mockStatement->shouldReceive('setFetchMode')->andReturn(true);
		$mockStatement->shouldReceive('bindValue')->andReturn(true);
		$mockStatement->shouldReceive('execute')->andReturn(true);
		$mockStatement->shouldReceive('fetchAll')->andReturn([
			(object) [
				'id' => 1,
				'photo_id' => 'photo-123',
				'type' => SizeVariantType::MEDIUM->value,
				'short_path' => 'uploads/medium/test.jpg',
				'short_path_watermarked' => null,
				'storage_disk' => StorageDiskType::LOCAL->value,
				'width' => 800,
				'height' => 600,
				'filesize' => 50000,
				'ratio' => 1.33,
			],
		]);

		$mockPdo = \Mockery::mock(\PDO::class);
		$mockPdo->shouldReceive('prepare')->andReturn($mockStatement);

		DB::connection()->setPdo($mockPdo);

		$mockWatermarker = \Mockery::mock(Watermarker::class);
		$mockWatermarker->shouldReceive('get_path')->andReturn('uploads/medium/test.jpg');
		$this->app->instance(Watermarker::class, $mockWatermarker);

		$mockUrlGen = \Mockery::mock(UrlGenerator::class);
		$mockUrlGen->shouldReceive('pathToUrl')->andReturn('https://example.com/cover.jpg');
		$this->app->instance(UrlGenerator::class, $mockUrlGen);

		$meta = $this->buildMeta();

		self::assertSame('https://example.com/cover.jpg', $meta->image_url);
		self::assertSame('Album', $meta->page_title);
	}

	public function testRenderReturnsView(): void
	{
		$meta = $this->buildMeta();

		$view = $meta->render();

		self::assertSame('components.meta', $view->name());
	}

	public function testNoSessionDataDoesNotAffectDefaults(): void
	{
		self::assertFalse(session()->has('access'));
		self::assertFalse(session()->has('album'));
		self::assertFalse(session()->has('photo'));

		$meta = $this->buildMeta();

		self::assertSame('My Gallery', $meta->page_title);
		self::assertSame('', $meta->page_description);
		self::assertSame('Test Owner', $meta->site_owner);
	}

	public function testUserCssAndJsUrlsAreSet(): void
	{
		$meta = $this->buildMeta();

		self::assertStringContainsString('user.css', $meta->user_css_url);
		self::assertStringContainsString('custom.js', $meta->user_js_url);
	}

	public function testAccessTrueInSessionAllowsProcessing(): void
	{
		$album = \Mockery::mock(AbstractAlbum::class);
		$album->shouldReceive('get_title')->andReturn('Visible Album');
		$album->shouldReceive('get_photos')->andReturn(new Collection());

		session(['access' => true, 'album' => $album]);

		$meta = $this->buildMeta();

		self::assertSame('Visible Album', $meta->page_title);
	}
}
