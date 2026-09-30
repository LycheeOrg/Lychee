<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Actions;

use App\Actions\Album\AutoCoverRows;
use App\Models\AlbumUserThumb;
use App\Models\User;
use Illuminate\Support\Collection;
use Tests\AbstractTestCase;

/**
 * Feature 076, FR-076-07 (Row Model read rule).
 */
class AutoCoverRowsTest extends AbstractTestCase
{
	private const OWNER_ID = 10;
	private const SHARED_ID = 20;
	private const OTHER_ID = 30;
	private const ADMIN_ID = 40;

	private function row(?int $user_id, string $photo_id): AlbumUserThumb
	{
		return new AlbumUserThumb(['album_id' => 'album-id', 'user_id' => $user_id, 'photo_id' => $photo_id]);
	}

	private function user(int $id, bool $is_admin = false): User
	{
		$user = new User();
		$user->id = $id;
		$user->may_administrate = $is_admin;

		return $user;
	}

	/**
	 * Owner row plus a public (`NULL`) row, as for an album with several permissions.
	 *
	 * @return Collection<int,AlbumUserThumb>
	 */
	private function publicRows(): Collection
	{
		return new Collection([$this->row(self::OWNER_ID, 'max'), $this->row(null, 'least')]);
	}

	/**
	 * Owner row plus a single-share row.
	 *
	 * @return Collection<int,AlbumUserThumb>
	 */
	private function singleShareRows(): Collection
	{
		return new Collection([$this->row(self::OWNER_ID, 'max'), $this->row(self::SHARED_ID, 'least')]);
	}

	public function testAdminGetsOwnerRow(): void
	{
		$admin = $this->user(self::ADMIN_ID, true);

		self::assertSame('max', AutoCoverRows::forViewer($this->publicRows(), self::OWNER_ID, $admin)?->photo_id);
		self::assertSame('max', AutoCoverRows::forViewer($this->singleShareRows(), self::OWNER_ID, $admin)?->photo_id);
	}

	public function testOwnerGetsOwnRow(): void
	{
		self::assertSame('max', AutoCoverRows::forViewer($this->publicRows(), self::OWNER_ID, $this->user(self::OWNER_ID))?->photo_id);
	}

	public function testSingleShareUserGetsOwnRow(): void
	{
		self::assertSame('least', AutoCoverRows::forViewer($this->singleShareRows(), self::OWNER_ID, $this->user(self::SHARED_ID))?->photo_id);
	}

	public function testOtherUserGetsPublicRow(): void
	{
		self::assertSame('least', AutoCoverRows::forViewer($this->publicRows(), self::OWNER_ID, $this->user(self::OTHER_ID))?->photo_id);
		self::assertNull(AutoCoverRows::forViewer($this->singleShareRows(), self::OWNER_ID, $this->user(self::OTHER_ID)));
	}

	public function testGuestGetsPublicRow(): void
	{
		self::assertSame('least', AutoCoverRows::forViewer($this->publicRows(), self::OWNER_ID, null)?->photo_id);
		self::assertNull(AutoCoverRows::forViewer($this->singleShareRows(), self::OWNER_ID, null));
	}

	public function testNoRowsYieldsNull(): void
	{
		self::assertNull(AutoCoverRows::forViewer(new Collection(), self::OWNER_ID, $this->user(self::OWNER_ID)));
		self::assertNull(AutoCoverRows::forViewer(new Collection(), self::OWNER_ID, null));
	}

	public function testPublicRow(): void
	{
		self::assertSame('least', AutoCoverRows::publicRow($this->publicRows())?->photo_id);
		self::assertNull(AutoCoverRows::publicRow($this->singleShareRows()));
	}
}
