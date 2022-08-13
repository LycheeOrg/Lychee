<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use Tests\Feature\Base\SharingTestBase;

class SharingAdvancedTest extends SharingTestBase
{
	/**
	 * Tests six albums with one photo each and varying protection settings.
	 *
	 * This is the test for [Bug #1155](https://github.com/LycheeOrg/Lychee/issues/1155).
	 * Scenario:
	 *
	 * ```
	 *  A       (public, password-protected "foo")
	 *  |
	 *  +-- B   (public)
	 *  |
	 *  +-- C   (public, password-protected "foo")
	 *  |
	 *  +-- D   (public, password-protected "foo", hidden)
	 *  |
	 *  +-- E   (public, password-protected "bar")
	 *  |
	 *  +-- F   (public, password-protected "bar", hidden)
	 * ```
	 *
	 * The anonymous user proceeds as follows:
	 *
	 *  1. Get root album view
	 *
	 *     _Expected result:_ Album A is visible, but without cover, it is still locked
	 *
	 *  2. Unlock albums with password "foo"
	 *
	 *     _Expected result:_
	 *      - Album A is visible with cover
	 *      - Album B is visible with cover
	 *      - Album C is visible with cover, as it became unlocked simultaneously
	 *      - Album D remains invisible
	 *      - Album E is visible without cover, as it is still locked
	 *      - Album F remains invisible
	 *
	 *  3. Directly access album D
	 *
	 *     _Expected result:_
	 *      - Access is granted without asking for a password as it has already been unlocked
	 *      - Image inside D is visible as part of D, but nowhere else
	 *
	 *  4. Directly access album F
	 *
	 *     _Expected result:_ Access is denied
	 *
	 *  5. Unlock albums with password "bar"
	 *
	 *     _Expected result:_
	 *      - Album A is visible with cover
	 *      - Album B is visible with cover
	 *      - Album C is visible with cover, as it became unlocked simultaneously
	 *      - Album D remains invisible
	 *      - Album E is visible with cover, as it became unlocked simultaneously
	 *      - Album F remains invisible
	 *
	 *  6. Directly access album F
	 *
	 *     _Expected result:_
	 *      - Access is granted without asking for a password as it has already been unlocked
	 *      - Image inside F is visible as part of F, but nowhere else
	 *
	 * In particular, each visibility check includes
	 *  - the content inside the album itself
	 *  - the album "Recent"
	 *  - the album tree
	 *
	 * @return void
	 */
	public function testSixAlbumsWithDifferentProtectionSettings(): void
	{
		static::markTestIncomplete('Not written yet');
	}
}
