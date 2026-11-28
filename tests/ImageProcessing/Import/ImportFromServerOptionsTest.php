<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\ImageProcessing\Import;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use function Safe\exec;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\CreatesImportTree;

class ImportFromServerOptionsTest extends BaseApiWithDataTest
{
	use CreatesImportTree;

	public function setUp(): void
	{
		parent::setUp();
		$this->createImportTree();
		$this->admin = $this->grantFullAccess($this->admin);
	}

	public function tearDown(): void
	{
		$this->removeImportTree();
		parent::tearDown();
	}

	public function testOptionsEndpointAsGuest(): void
	{
		$response = $this->getJson('/Import');
		$this->assertUnauthorized($response);
	}

	public function testOptionsEndpointLoggedIn(): void
	{
		$response = $this->actingAs($this->userLocked)->getJson('/Import');
		$this->assertForbidden($response);
	}

	public function testOptionsEndpointAsOwner(): void
	{
		$response = $this->actingAs($this->admin)->getJson('/Import');
		$this->assertOk($response);
	}

	/** S-115-01: no server path → no import, admins included. */
	public function testNoServerPathForbidden(): void
	{
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->getJson('/Import'));

		$this->admin->server_path = null;
		$this->admin->save();
		$this->assertForbidden($this->actingAs($this->admin->fresh())->getJson('/Import'));
	}

	/** S-118-09 (amends S-115-02): a path is enough, no upload right needed (FR-118-14). */
	public function testNoUploadRightAllowed(): void
	{
		$user = User::factory()->create();
		$user->server_path = $this->import_root;
		$user->may_upload = false;
		$user->save();

		$this->assertOk($this->actingAs($user->fresh())->getJson('/Import'));
	}

	/** S-115-03: the .env switch still wins for admins. */
	public function testDisabledFeatureForbiddenForAdmin(): void
	{
		config(['features.disable-import-from-server' => true]);
		$this->assertForbidden($this->actingAs($this->admin)->getJson('/Import'));
	}

	/** S-115-04 */
	public function testFullAccessOptions(): void
	{
		$response = $this->actingAs($this->admin)->getJson('/Import');
		$this->assertOk($response);
		$response->assertJson([
			'directory' => base_path(''),
			'is_root_folder_album' => true,
		]);
	}

	/** S-115-05 */
	public function testRestrictedOptions(): void
	{
		DB::table('configs')->where('key', '=', 'import_via_symlink')->update(['value' => '1']);
		DB::table('configs')->where('key', '=', 'import_root_folder_as_album')->update(['value' => '0']);
		$user = $this->restrictTo($this->userMayUpload1, $this->import_root);

		$response = $this->actingAs($user)->getJson('/Import');
		$this->assertOk($response);
		$response->assertJson([
			'directory' => $this->import_root,
			'is_root_folder_album' => false,
			'import_via_symlink' => true,
		]);
	}

	/** S-115-20 */
	public function testMissingImportRoot(): void
	{
		$user = $this->restrictTo($this->userMayUpload1, $this->import_root);
		exec('rm -rf ' . escapeshellarg($this->import_root));

		$response = $this->actingAs($user)->getJson('/Import');
		$this->assertUnprocessable($response);
		$response->assertSee('Import directory is not available');
	}

	/** The album rights follow the policy. */
	public function testRootAlbumRights(): void
	{
		$user = $this->restrictTo($this->userMayUpload1, $this->import_root);
		$response = $this->actingAs($user)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJsonPath('rights.can_import_from_server', true);
		$response = $this->actingAs($this->userMayUpload2)->getJson('Albums');
		$response->assertJsonPath('rights.can_import_from_server', false);
	}
}
