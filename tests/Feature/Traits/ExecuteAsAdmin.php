<?php

namespace Tests\Feature\Traits;

/**
 * This trait allows BasePhotoTests to be executed as Admin directly.
 */
trait ExecuteAsAdmin
{
	/**
	 * We just return the ID of admin, i.e. 1.
	 *
	 * @return int
	 */
	protected function executeAs(): int
	{
		return 1;
	}

	/**
	 * Nothing to do, admin user always exists.
	 *
	 * @return void
	 */
	protected function logoutAs(): void
	{
	}
}