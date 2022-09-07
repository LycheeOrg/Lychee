<?php

namespace App\DTO;

class LycheeGitInfo extends DTO
{
	public string $branch;
	public string $commit;
	public string $additional;

	public function __construct(string $branch, string $commit, string $additional)
	{
		$this->branch = $branch;
		$this->commit = $commit;
		$this->additional = $additional;
	}

	public function toString(): string
	{
		return sprintf('%s (%s) -- %s', $this->branch, $this->commit, $this->additional);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'branch' => $this->branch,
			'commit' => $this->commit,
			'additional' => $this->additional,
		];
	}
}
