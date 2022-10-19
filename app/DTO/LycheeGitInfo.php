<?php

namespace App\DTO;

use function Safe\sprintf;

class LycheeGitInfo extends ArrayableDTO
{
	public function __construct(
		public string $branch,
		public string $commit,
		public string $additional
	) {
	}

	public function toString(): string
	{
		return sprintf('%s (%s) -- %s', $this->branch, $this->commit, $this->additional);
	}
}
