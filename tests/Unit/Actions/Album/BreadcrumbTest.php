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

namespace Tests\Unit\Actions\Album;

use App\Actions\Album\Breadcrumb;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;

class BreadcrumbTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function breadcrumb(): Breadcrumb
	{
		return new Breadcrumb(new AlbumQueryPolicy());
	}

	/**
	 * Sets the owner of an album directly in the database.
	 *
	 * Album::fixOwnershipOfChildren() (triggered by AlbumFactory::children_of())
	 * propagates a parent's owner down to its whole subtree as soon as another
	 * child is appended anywhere in the chain. To build a tree with mixed
	 * ownership for these tests, ownership is therefore fixed up directly in
	 * the database only after the whole tree has been created.
	 */
	private function setOwner(Album $album, User $user): void
	{
		DB::table('base_albums')->where('id', $album->id)->update(['owner_id' => $user->id]);
	}

	public function testNoAncestorsReturnsEmptyArray(): void
	{
		$user = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Root']);

		$this->actingAs($user);

		$result = $this->breadcrumb()->do($root);

		$this->assertSame([], $result);
	}

	public function testReturnsFullBreadcrumbInRootToLeafOrderWhenAllAccessible(): void
	{
		$user = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Root', 'slug' => 'root-slug']);
		$mid = Album::factory()->children_of($root)->owned_by($user)->create(['title' => 'Middle', 'slug' => 'mid-slug']);
		$leaf = Album::factory()->children_of($mid)->owned_by($user)->create(['title' => 'Leaf']);
		$leaf->refresh();

		$this->actingAs($user);

		$result = $this->breadcrumb()->do($leaf);

		$this->assertCount(2, $result);
		$this->assertSame($root->id, $result[0]->id);
		$this->assertSame('Root', $result[0]->title);
		$this->assertSame('root-slug', $result[0]->slug);
		$this->assertSame($mid->id, $result[1]->id);
		$this->assertSame('Middle', $result[1]->title);
		$this->assertSame('mid-slug', $result[1]->slug);
	}

	public function testAdminSeesFullBreadcrumbRegardlessOfOwnership(): void
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();
		$admin = User::factory()->may_administrate()->create();

		// Build the chain with a valid owner throughout (the factory's default
		// owner_id does not reference an existing user and would violate the
		// foreign key), then reassign the middle album to someone else entirely.
		$root = Album::factory()->as_root()->owned_by($owner)->create(['title' => 'Root']);
		$mid = Album::factory()->children_of($root)->owned_by($owner)->create(['title' => 'Middle']);
		$leaf = Album::factory()->children_of($mid)->owned_by($owner)->create(['title' => 'Leaf']);
		$leaf->refresh();

		$this->setOwner($mid, $other);

		$this->actingAs($admin);

		$result = $this->breadcrumb()->do($leaf);

		$this->assertCount(2, $result);
		$this->assertSame($root->id, $result[0]->id);
		$this->assertSame($mid->id, $result[1]->id);
	}

	public function testInaccessibleAncestorAndEverythingAboveItIsMaskedAndCollapsed(): void
	{
		$user = User::factory()->create();
		$other = User::factory()->create();

		// Build the chain with a valid owner throughout (the factory's default
		// owner_id does not reference an existing user and would violate the
		// foreign key), then reassign just the middle album: root (owned by
		// $user, would be accessible on its own) -> private (owned by $other,
		// NOT shared) -> accessible (owned by $user) -> leaf.
		$root = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Root']);
		$private = Album::factory()->children_of($root)->owned_by($user)->create(['title' => 'Private']);
		$accessible = Album::factory()->children_of($private)->owned_by($user)->create(['title' => 'Accessible']);
		$leaf = Album::factory()->children_of($accessible)->owned_by($user)->create(['title' => 'Leaf']);
		$leaf->refresh();

		$this->setOwner($private, $other);

		$this->actingAs($user);

		$result = $this->breadcrumb()->do($leaf);

		// Even though `root` is independently accessible to $user, the broken
		// link at `private` masks everything from that point up to the root,
		// and consecutive masked entries collapse into a single placeholder.
		$this->assertCount(2, $result);
		$this->assertNull($result[0]->id);
		$this->assertSame('...', $result[0]->title);
		$this->assertNull($result[0]->slug);
		$this->assertSame($accessible->id, $result[1]->id);
		$this->assertSame('Accessible', $result[1]->title);
	}
}
