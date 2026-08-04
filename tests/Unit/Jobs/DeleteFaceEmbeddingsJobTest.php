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

namespace Tests\Unit\Jobs;

use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Services\Image\FacialRecognitionService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class DeleteFaceEmbeddingsJobTest extends AbstractTestCase
{
	public function testDoesNothingForEmptyFaceIds(): void
	{
		$service = \Mockery::mock(FacialRecognitionService::class);
		$service->shouldNotReceive('isConfigured');

		(new DeleteFaceEmbeddingsJob([]))->handle($service);
		self::assertTrue(true);
	}

	public function testWarnsWhenServiceNotConfigured(): void
	{
		$service = \Mockery::mock(FacialRecognitionService::class);
		$service->shouldReceive('isConfigured')->once()->andReturn(false);

		Log::shouldReceive('warning')->once()->with('DeleteFaceEmbeddingsJob: AI Vision service not configured.');

		(new DeleteFaceEmbeddingsJob(['face-1']))->handle($service);
		self::assertTrue(true);
	}

	public function testWarnsOnUnsuccessfulResponse(): void
	{
		$service = \Mockery::mock(FacialRecognitionService::class);
		$service->shouldReceive('isConfigured')->once()->andReturn(true);

		$response = \Mockery::mock(Response::class);
		$response->shouldReceive('successful')->once()->andReturn(false);
		$response->shouldReceive('status')->once()->andReturn(500);
		$service->shouldReceive('deleteEmbeddings')->once()->with(['face-1'])->andReturn($response);

		Log::shouldReceive('warning')->once()->with('DeleteFaceEmbeddingsJob: /embeddings DELETE returned HTTP 500.', ['face_ids' => ['face-1']]);

		(new DeleteFaceEmbeddingsJob(['face-1']))->handle($service);
		self::assertTrue(true);
	}

	public function testSucceedsSilentlyOnSuccessfulResponse(): void
	{
		$service = \Mockery::mock(FacialRecognitionService::class);
		$service->shouldReceive('isConfigured')->once()->andReturn(true);

		$response = \Mockery::mock(Response::class);
		$response->shouldReceive('successful')->once()->andReturn(true);
		$service->shouldReceive('deleteEmbeddings')->once()->with(['face-1'])->andReturn($response);

		Log::shouldReceive('warning')->never();

		(new DeleteFaceEmbeddingsJob(['face-1']))->handle($service);
		self::assertTrue(true);
	}

	public function testWarnsWhenRequestThrows(): void
	{
		$service = \Mockery::mock(FacialRecognitionService::class);
		$service->shouldReceive('isConfigured')->once()->andReturn(true);
		$service->shouldReceive('deleteEmbeddings')->once()->with(['face-1'])->andThrow(new \Exception('connection refused'));

		Log::shouldReceive('warning')->once()->with('DeleteFaceEmbeddingsJob: request failed: connection refused', ['face_ids' => ['face-1']]);

		(new DeleteFaceEmbeddingsJob(['face-1']))->handle($service);
		self::assertTrue(true);
	}
}
