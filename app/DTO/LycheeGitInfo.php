<?php

namespace App\DTO;

use App\Contracts\GitHubVersionControl;

class LycheeGitInfo extends ArrayableDTO
{
	public string $branch;
	public string $commit;
	public string $additional;

	public function __construct(GitHubVersionControl $gvc)
	{
		$this->branch = $gvc->localBranch ?? '??';
		$this->commit = $gvc->localHead ?? '??';
		$this->additional = $gvc->getBehindTest();
	}

	public function toString(): string
	{
		return sprintf('%s (%s) -- %s', $this->branch, $this->commit, $this->additional);
	}
}
