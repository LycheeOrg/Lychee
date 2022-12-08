<?php

namespace App\DTO;

use App\Contracts\GitHubVersionControl;

class LycheeGitInfo extends ArrayableDTO
{
	public string $branch;
	public string $commit;
	public string $additional;

	public function __construct(?GitHubVersionControl $gvc)
	{
		$this->branch = $gvc?->localBranch ?? '??';
		$this->commit = $gvc?->localHead ?? '??';

		$this->additional = match ($gvc?->countBehind) {
			null => '??',
			false => 'Could not compare.',
			0 => sprintf('Up to date (%s).', $gvc->age),
			30 => sprintf('More than 30 commits behind master (%s).', $gvc->age),
			default => sprintf('%d commits behind master %s (%s)', $gvc->countBehind, $gvc->remoteHead, $gvc->age)
		};
	}

	public function toString(): string
	{
		return sprintf('%s (%s) -- %s', $this->branch, $this->commit, $this->additional);
	}
}
