<?php

namespace App\DTO;

class LycheeGitInfo extends DTO
{
	public string $branch;
	public ?string $commit;
	public ?string $additional;

	public function __construct(string $branch, ?string $commit = null, ?string $additional = null)
	{
		$this->branch = $branch;
		$this->commit = $commit;
		$this->additional = $additional;
	}

	public function toString(): string
	{
		$ret = $this->branch;
		$ret .= $this->commit ? ' (' . $this->commit . ')' : '';
		$ret .= $this->additional ? ' -- ' . $this->additional : '';

		return $ret;
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
