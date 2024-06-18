<?php

declare(strict_types=1);

namespace App\Metadata\Versions;

trait Trimable
{
	/**
	 * Given a commit id, return the 7 first characters (7 hex digits) and trim it to remove \n.
	 *
	 * @param string $commit_id
	 *
	 * @return string
	 */
	protected function trim(string $commit_id): string
	{
		return trim(substr($commit_id, 0, 7));
	}
}