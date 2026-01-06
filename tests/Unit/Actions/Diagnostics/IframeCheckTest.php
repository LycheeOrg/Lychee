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

namespace Tests\Unit\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\IframeCheck;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;

class IframeCheckTest extends AbstractTestCase
{
	private IframeCheck $iframeCheck;
	private array $data;
	private \Closure $next;

	protected function setUp(): void
	{
		parent::setUp();
		$this->iframeCheck = new IframeCheck();
		$this->data = [];
		$this->next = function (array $data) {
			return $data;
		};
	}

	/**
	 * Test handle method when X-Frame-Options is set to 'deny'.
	 * Should skip all checks and return next result.
	 *
	 * @return void
	 */
	public function testHandleWhenXFrameOptionsDeny(): void
	{
		Config::set('secure-headers.x-frame-options', 'deny');

		$result = $this->iframeCheck->handle($this->data, $this->next);

		$this->assertEmpty($result, 'Should return empty result when X-Frame-Options is deny');
	}

	/**
	 * Test handle method when X-Frame-Options is not 'deny' but no frame ancestors are configured.
	 * Should add warning about CSP frame ancestors being set.
	 *
	 * @return void
	 */
	public function testHandleWhenXFrameOptionsNotDenyWithEmptyFrameAncestors(): void
	{
		Config::set('secure-headers.x-frame-options', 'sameorigin');
		Config::set('secure-headers.csp.frame-ancestors.allow', []);
		Config::set('session.same_site', 'lax');
		Config::set('session.secure', true);

		$result = $this->iframeCheck->handle($this->data, $this->next);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertEquals(MessageType::WARNING, $result[0]->type);
		$this->assertEquals('SECURITY_HEADER_CSP_FRAME_ANCESTORS is set.', $result[0]->message);
		$this->assertEquals(IframeCheck::class, $result[0]->from);
		$this->assertContains('This allows Lychee to be used in iFrame, which is not recommended as it will lower the security of your session cookies.', $result[0]->details);
	}

	/**
	 * Test handle method when X-Frame-Options is not 'deny' and frame ancestors are configured.
	 * Should add warning with censored frame ancestor URLs.
	 *
	 * @return void
	 */
	public function testHandleWhenXFrameOptionsNotDenyWithFrameAncestors(): void
	{
		Config::set('secure-headers.x-frame-options', 'sameorigin');
		Config::set('secure-headers.csp.frame-ancestors.allow', ['https://example.com', 'https://trusted-site.org']);
		Config::set('session.same_site', 'lax');
		Config::set('session.secure', true);

		$result = $this->iframeCheck->handle($this->data, $this->next);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertEquals(MessageType::WARNING, $result[0]->type);
		$this->assertEquals('SECURITY_HEADER_CSP_FRAME_ANCESTORS is set.', $result[0]->message);
		$this->assertEquals(IframeCheck::class, $result[0]->from);

		// Should contain censored URLs
		$detailsText = implode(' ', $result[0]->details);
		$this->assertStringContainsString('Allowing', $detailsText);
		$this->assertStringContainsString('to use Lychee in iFrame.', $detailsText);
		$this->assertContains('This allows Lychee to be used in iFrame, which is not recommended as it will lower the security of your session cookies.', $result[0]->details);
	}

	/**
	 * Test handle method when session same_site is 'none' and secure is false.
	 * Should add error about insecure session configuration.
	 *
	 * @return void
	 */
	public function testHandleWhenSessionSameSiteNoneAndSecureFalse(): void
	{
		Config::set('secure-headers.x-frame-options', 'sameorigin');
		Config::set('secure-headers.csp.frame-ancestors.allow', []);
		Config::set('session.same_site', 'none');
		Config::set('session.secure', false);

		$result = $this->iframeCheck->handle($this->data, $this->next);

		$this->assertCount(2, $result);

		// First diagnostic should be the warning about CSP frame ancestors
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertEquals(MessageType::WARNING, $result[0]->type);
		$this->assertEquals('SECURITY_HEADER_CSP_FRAME_ANCESTORS is set.', $result[0]->message);

		// Second diagnostic should be the error about session configuration
		$this->assertInstanceOf(DiagnosticData::class, $result[1]);
		$this->assertEquals(MessageType::ERROR, $result[1]->type);
		$this->assertEquals('Session same_site is set to none, but session secure is set to false.', $result[1]->message);
		$this->assertEquals(IframeCheck::class, $result[1]->from);
		$this->assertEquals(['Set SESSION_SECURE_COOKIE to true in your .env file to solve this issue.'], $result[1]->details);
	}

	/**
	 * Test handle method when session same_site is 'none' but secure is true.
	 * Should only add warning about CSP frame ancestors, not session error.
	 *
	 * @return void
	 */
	public function testHandleWhenSessionSameSiteNoneAndSecureTrue(): void
	{
		Config::set('secure-headers.x-frame-options', 'sameorigin');
		Config::set('secure-headers.csp.frame-ancestors.allow', []);
		Config::set('session.same_site', 'none');
		Config::set('session.secure', true);

		$result = $this->iframeCheck->handle($this->data, $this->next);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertEquals(MessageType::WARNING, $result[0]->type);
		$this->assertEquals('SECURITY_HEADER_CSP_FRAME_ANCESTORS is set.', $result[0]->message);
	}

	/**
	 * Test handle method when session same_site is not 'none' and secure is false.
	 * Should only add warning about CSP frame ancestors, not session error.
	 *
	 * @return void
	 */
	public function testHandleWhenSessionSameSiteNotNoneAndSecureFalse(): void
	{
		Config::set('secure-headers.x-frame-options', 'sameorigin');
		Config::set('secure-headers.csp.frame-ancestors.allow', []);
		Config::set('session.same_site', 'lax');
		Config::set('session.secure', false);

		$result = $this->iframeCheck->handle($this->data, $this->next);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertEquals(MessageType::WARNING, $result[0]->type);
		$this->assertEquals('SECURITY_HEADER_CSP_FRAME_ANCESTORS is set.', $result[0]->message);
	}

	/**
	 * Test handle method with existing data in the array.
	 * Should preserve existing data and add new diagnostics.
	 *
	 * @return void
	 */
	public function testHandleWithExistingData(): void
	{
		$existingDiagnostic = DiagnosticData::info('Existing diagnostic', 'TestClass');
		$this->data = [$existingDiagnostic];

		Config::set('secure-headers.x-frame-options', 'sameorigin');
		Config::set('secure-headers.csp.frame-ancestors.allow', ['https://example.com']);
		Config::set('session.same_site', 'none');
		Config::set('session.secure', false);

		$result = $this->iframeCheck->handle($this->data, $this->next);

		$this->assertCount(3, $result);
		$this->assertEquals($existingDiagnostic, $result[0]); // Should preserve existing data
		$this->assertEquals(MessageType::WARNING, $result[1]->type);
		$this->assertEquals(MessageType::ERROR, $result[2]->type);
	}

	/**
	 * Test handle method with different X-Frame-Options values.
	 * Should process checks for any value other than 'deny'.
	 *
	 * @return void
	 */
	public function testHandleWithDifferentXFrameOptionsValues(): void
	{
		$xFrameOptionsValues = ['sameorigin', 'allow-from https://example.com', '', null];

		foreach ($xFrameOptionsValues as $value) {
			Config::set('secure-headers.x-frame-options', $value);
			Config::set('secure-headers.csp.frame-ancestors.allow', []);
			Config::set('session.same_site', 'lax');
			Config::set('session.secure', true);

			$this->data = []; // Reset data for each iteration

			$result = $this->iframeCheck->handle($this->data, $this->next);

			$this->assertCount(1, $result, 'Failed for X-Frame-Options value: ' . var_export($value, true));
			$this->assertEquals(MessageType::WARNING, $result[0]->type);
		}
	}

	/**
	 * Test that the closure next function is called and returns the modified data.
	 *
	 * @return void
	 */
	public function testNextClosureIsCalled(): void
	{
		$nextCalled = false;
		$nextFunction = function (array $data) use (&$nextCalled) {
			$nextCalled = true;

			return $data;
		};

		Config::set('secure-headers.x-frame-options', 'sameorigin');
		Config::set('secure-headers.csp.frame-ancestors.allow', []);
		Config::set('session.same_site', 'lax');
		Config::set('session.secure', true);

		$this->iframeCheck->handle($this->data, $nextFunction);

		$this->assertTrue($nextCalled, 'Next closure should be called');
	}

	/**
	 * Test that the method passes data by reference correctly.
	 *
	 * @return void
	 */
	public function testDataPassedByReference(): void
	{
		Config::set('secure-headers.x-frame-options', 'sameorigin');
		Config::set('secure-headers.csp.frame-ancestors.allow', []);
		Config::set('session.same_site', 'lax');
		Config::set('session.secure', true);

		$originalData = [];
		$this->iframeCheck->handle($originalData, $this->next);

		$this->assertCount(1, $originalData, 'Original data array should be modified by reference');
		$this->assertInstanceOf(DiagnosticData::class, $originalData[0]);
	}
}