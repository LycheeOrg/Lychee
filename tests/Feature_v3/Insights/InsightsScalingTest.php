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

namespace Tests\Feature_v3\Insights;

use App\Actions\Insights\ComputeInsights;
use App\DTO\Insights\InsightsPeriod;
use App\DTO\Insights\InsightsScope;
use App\Http\Resources\Insights\InsightsResource;
use App\Models\Album;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers the linear computation of Insights (Feature 108, FR-108-02,
 * NFR-108-01, NFR-108-02, S-108-01 … S-108-03, S-108-05).
 */
class InsightsScalingTest extends BaseApiWithDataTest
{
	use InsightsDataset;

	private Album $album_a1;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		$this->seedInsightsDataset();

		// A sub-album of A holding a photo of someone else, plus p1 and p2 a second time.
		$this->album_a1 = Album::factory()->children_of($this->album_a)->owned_by($this->photographer)->create();
		Photo::factory()->owned_by($this->userMayUpload2)->in($this->album_a1)->create(['taken_at' => new Carbon('2025-05-05 12:00:00', 'UTC')]);
		$this->p1->albums()->attach($this->album_a1->id);
		$this->p2->albums()->attach($this->album_a1->id);
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	/**
	 * @return array<string,array{0:string,1:string}>
	 */
	public static function scopesAndPeriods(): array
	{
		$cases = [];
		foreach (['owner', 'whole', 'album', 'sub-album'] as $scope) {
			foreach (['library', 'year', 'range', 'day'] as $period) {
				$cases[$scope . ' ' . $period] = [$scope, $period];
			}
		}

		return $cases;
	}

	#[DataProvider('scopesAndPeriods')]
	public function testPageSizeDoesNotChangeTheResult(string $scope, string $period): void
	{
		$by_one = (new ComputeInsights(chunk: 1))->do($this->scope($scope), $this->period($period));
		$by_default = (new ComputeInsights())->do($this->scope($scope), $this->period($period));

		self::assertSame($by_default->toArray(), $by_one->toArray());
		self::assertGreaterThan(0, $by_default->overview->total);
	}

	public function testPhotoInTwoAlbumsOfTheTreeCountsOnce(): void
	{
		$insights = (new ComputeInsights(chunk: 1))->do($this->scope('album'), InsightsPeriod::library());

		// p1, p2 (both in A and A1) and the photo of another owner in A1.
		self::assertSame(3, $insights->overview->total);
		self::assertSame(2, $insights->overview->albums);
	}

	public function testSideCountsDoNotGrowWithLinksOrFaces(): void
	{
		$scope = InsightsScope::owner($this->photographer->id);
		$period = InsightsPeriod::year(2025);
		[$before, $queries_before] = $this->measure($scope, $period);

		// p1 sits on the edge of 2025 (stored in 2024 UTC), p2 and p4 inside it.
		$albums = Album::factory()->as_root()->owned_by($this->photographer)->count(3)->create()->pluck('id')->all();
		foreach ([$this->p1, $this->p2, $this->p4] as $photo) {
			$photo->albums()->attach($albums);
		}
		foreach ([$this->p1, $this->p2, $this->p4] as $photo) {
			Face::factory()->for_photo($photo)->for_person(Person::factory()->create())->create();
		}
		[$after, $queries_after] = $this->measure($scope, $period);

		self::assertSame($before->overview->albums + 3, $after->overview->albums);
		self::assertSame($before->people->people + 3, $after->people->people);
		self::assertSame($queries_before, $queries_after);
	}

	public function testAlbumScopeBindsAFixedNumberOfParameters(): void
	{
		$before = $this->maxBindings($this->scope('album'));

		Album::factory()->children_of($this->album_a1)->owned_by($this->photographer)->count(5)->create();
		$after = $this->maxBindings($this->scope('album'));

		// Every query binds as many values with 7 albums in the tree as with 2.
		self::assertSame($before, $after);
	}

	public function testOneDayRangeCountsFromTheEdges(): void
	{
		$response = $this->actingAs($this->photographer)->getJsonV3('Insights', ['period' => 'range', 'from' => '2025-01-01', 'to' => '2025-01-01']);

		$this->assertOk($response);
		// p1 only (08:30 at +09:00); p2 (2025-01-02 10:00 UTC) is in the widened window but not on that day.
		$response->assertJsonPath('overview.total', 1);
		$response->assertJsonPath('overview.albums', 2);
		$response->assertJsonPath('people.people', 1);
	}

	/**
	 * @return array{0:InsightsResource,1:int}
	 */
	private function measure(InsightsScope $scope, InsightsPeriod $period): array
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$insights = (new ComputeInsights(chunk: 1))->do($scope, $period);
		$queries = count(DB::getQueryLog());
		DB::disableQueryLog();

		return [$insights, $queries];
	}

	/**
	 * Largest number of bound values of one query while computing the scope's Year 2025.
	 */
	private function maxBindings(InsightsScope $scope): int
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		(new ComputeInsights(chunk: 1))->do($scope, InsightsPeriod::year(2025));
		$bindings = array_map(fn (array $query) => count($query['bindings']), DB::getQueryLog());
		DB::disableQueryLog();

		return max($bindings);
	}

	private function scope(string $name): InsightsScope
	{
		return match ($name) {
			'owner' => InsightsScope::owner($this->photographer->id),
			'whole' => InsightsScope::wholeInstance(),
			'album' => $this->albumScope($this->album_a),
			default => $this->albumScope($this->album_a1),
		};
	}

	private function albumScope(Album $album): InsightsScope
	{
		$album->refresh();

		return InsightsScope::album($album->id, $album->_lft, $album->_rgt);
	}

	private function period(string $name): InsightsPeriod
	{
		return match ($name) {
			'library' => InsightsPeriod::library(),
			'year' => InsightsPeriod::year(2025),
			'range' => InsightsPeriod::range('2025-01-01', '2025-05-31'),
			default => InsightsPeriod::range('2025-01-01', '2025-01-01'),
		};
	}
}
