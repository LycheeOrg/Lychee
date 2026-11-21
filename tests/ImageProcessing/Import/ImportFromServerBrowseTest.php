<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\ImageProcessing\Import;

use App\Models\User;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\CreatesImportTree;

class ImportFromServerBrowseTest extends BaseApiWithDataTest
{
	use CreatesImportTree;

	private User $alice;

	public function setUp(): void
	{
		parent::setUp();
		$this->createImportTree();
		$this->admin = $this->grantFullAccess($this->admin);
		$this->alice = $this->restrictTo($this->userMayUpload1, $this->import_root);
	}

	public function tearDown(): void
	{
		$this->removeImportTree();
		parent::tearDown();
	}

	private function browse(User $user, string $directory): \Illuminate\Testing\TestResponse
	{
		return $this->actingAs($user)->getJsonWithData('/Import::browse', ['directory' => $directory]);
	}

	/**
	 * @return string[]
	 */
	private function sorted(\Illuminate\Testing\TestResponse $response): array
	{
		$content = $response->json();
		sort($content);

		return $content;
	}

	/**
	 * Drops the debug-mode `trace` entries, at every level of
	 * `previous_exception`: they hold the line of the test that made the call.
	 */
	private function withoutTrace(mixed $body): mixed
	{
		if (!is_array($body)) {
			return $body;
		}
		unset($body['trace']);

		return array_map(fn (mixed $value) => $this->withoutTrace($value), $body);
	}

	public function testBrowseEndpointAsGuest(): void
	{
		$response = $this->getJsonWithData('/Import::browse', ['directory' => 'tests']);
		$this->assertUnauthorized($response);
	}

	public function testBrowseEndpointLoggedIn(): void
	{
		$this->assertForbidden($this->browse($this->userLocked, 'tests'));
	}

	public function testBrowseEndpointAsOwnerWrongDir(): void
	{
		$this->assertUnprocessable($this->browse($this->admin, 'wrong-dir'));
	}

	public function testBrowseEndpointAsOwner(): void
	{
		$response = $this->browse($this->admin, 'tests');
		$this->assertOk($response);
		self::assertEquals(['..', 'AssistedVision', 'Constants', 'Feature_v2', 'Feature_v3', 'Fixtures', 'ImageProcessing', 'Install', 'Precomputing', 'Samples', 'Support', 'Traits', 'Unit', 'Webshop', 'docker'], $this->sorted($response));
	}

	/** S-115-04: links are never listed, even with full access. */
	public function testFullAccessSeesNoLinks(): void
	{
		$response = $this->browse($this->admin, $this->import_root);
		$this->assertOk($response);
		self::assertEquals(['..', '2026', 'Family'], $this->sorted($response));
	}

	/** S-115-05, S-115-21, S-115-22: no `..` at the root, no links. */
	public function testRestrictedAtRoot(): void
	{
		$response = $this->browse($this->alice, $this->import_root);
		$this->assertOk($response);
		self::assertEquals(['2026', 'Family'], $this->sorted($response));
	}

	/** S-115-05: below the root `..` is listed. */
	public function testRestrictedBelowRoot(): void
	{
		$response = $this->browse($this->alice, $this->import_root . '/Family');
		$this->assertOk($response);
		self::assertEquals(['..'], $this->sorted($response));
	}

	/** S-115-21: a link named explicitly is refused, for every user, however it is spelled. */
	public function testNamedLinkRefused(): void
	{
		$this->assertUnprocessable($this->browse($this->alice, $this->import_root . '/link-inside'));
		$this->assertUnprocessable($this->browse($this->admin, $this->import_root . '/link-inside'));
		$this->assertUnprocessable($this->browse($this->admin, $this->import_root . '/link-inside/'));
		$this->assertUnprocessable($this->browse($this->admin, $this->import_root . '/link-inside/.'));
	}

	/** S-115-06: existing and missing outside paths give the same answer. */
	public function testOutsidePathsLookAlike(): void
	{
		$existing = $this->browse($this->alice, '/etc');
		$missing = $this->browse($this->alice, '/does/not/exist');
		$this->assertForbidden($existing);
		$this->assertForbidden($missing);
		self::assertSame($this->withoutTrace($existing->json()), $this->withoutTrace($missing->json()));
	}

	/** S-115-07 */
	public function testLexicalEscapeForbidden(): void
	{
		$this->assertForbidden($this->browse($this->alice, $this->import_root . '/../outside'));
	}

	/** S-115-08 */
	public function testPrefixTrapForbidden(): void
	{
		$this->assertForbidden($this->browse($this->alice, $this->import_base . '/alice2'));
	}

	/** S-115-22: browsing through an outside link is refused. */
	public function testOutsideLinkForbidden(): void
	{
		$this->assertForbidden($this->browse($this->alice, $this->import_root . '/link-outside'));
	}

	public function testRelativePathForbidden(): void
	{
		$this->assertForbidden($this->browse($this->alice, 'tests'));
	}

	public function testMissingInsideIsUnprocessable(): void
	{
		$this->assertUnprocessable($this->browse($this->alice, $this->import_root . '/nope'));
	}
}
