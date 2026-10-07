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
use App\Models\Photo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers the Insights cache keyed on scope, period and library revision
 * (Feature 085, NFR-085-06, S-085-17).
 */
class InsightsCacheTest extends BaseApiWithDataTest
{
	use InsightsDataset;

	private int $computations = 0;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		$this->seedInsightsDataset();
		Cache::flush();

		$test = $this;
		$this->app->instance(ComputeInsights::class, new class($test) extends ComputeInsights {
			public function __construct(private InsightsCacheTest $test)
			{
				parent::__construct();
			}

			public function do(InsightsScope $scope, InsightsPeriod $period): InsightsResource
			{
				$this->test->countComputation();

				return parent::do($scope, $period);
			}
		});
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function countComputation(): void
	{
		$this->computations++;
	}

	public function testUnchangedLibraryIsServedFromCache(): void
	{
		$this->insights(['period' => 'library']);
		$response = $this->insights(['period' => 'library']);

		self::assertSame(1, $this->computations);
		$response->assertJsonPath('overview.total', 6);
		self::assertNotNull($response->json('time_span.first.thumb_url'));
	}

	public function testEditInvalidates(): void
	{
		$this->insights(['period' => 'library']);
		$this->travel(2)->seconds();
		$this->p2->is_highlighted = true;
		$this->p2->save();

		$response = $this->insights(['period' => 'library']);

		self::assertSame(2, $this->computations);
		$response->assertJsonPath('overview.highlighted', 2);
	}

	public function testUploadInvalidates(): void
	{
		$this->insights(['period' => 'library']);
		Photo::factory()->owned_by($this->photographer)->create();

		$response = $this->insights(['period' => 'library']);

		self::assertSame(2, $this->computations);
		$response->assertJsonPath('overview.total', 7);
	}

	public function testDeleteInvalidates(): void
	{
		$this->insights(['period' => 'library']);
		Queue::fake();
		$this->p5->delete();

		$response = $this->insights(['period' => 'library']);

		self::assertSame(2, $this->computations);
		$response->assertJsonPath('overview.total', 5);
	}

	public function testAlbumChangeInvalidates(): void
	{
		$this->insights(['period' => 'library']);
		$this->album_c->delete();

		$response = $this->insights(['period' => 'library']);

		self::assertSame(2, $this->computations);
		$response->assertJsonPath('overview.albums', 2);
	}

	public function testAnotherOwnersChangeKeepsTheCache(): void
	{
		$this->insights(['period' => 'library']);
		Photo::factory()->owned_by($this->userMayUpload2)->create();

		$this->insights(['period' => 'library']);

		self::assertSame(1, $this->computations);
	}

	public function testScopesAndPeriodsAreCachedApart(): void
	{
		$this->insights(['period' => 'library']);
		$this->insights(['period' => 'year', 'year' => 2025]);
		$this->assertOk($this->actingAs($this->admin)->getJsonV3('Insights', ['period' => 'library', 'whole_instance' => 1]));

		self::assertSame(3, $this->computations);

		// The owner scope is the same whoever asks for it.
		$this->assertOk($this->actingAs($this->admin)->getJsonV3('Insights', ['period' => 'library', 'owner_id' => $this->photographer->id]));
		self::assertSame(3, $this->computations);
	}

	/**
	 * @param array<string,mixed> $data
	 */
	private function insights(array $data): \Illuminate\Testing\TestResponse
	{
		$response = $this->actingAs($this->photographer)->getJsonV3('Insights', $data);
		$this->assertOk($response);

		return $response;
	}
}
