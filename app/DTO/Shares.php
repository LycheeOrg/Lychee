<?php

namespace App\DTO;

use Illuminate\Support\Collection;

class Shares extends DTO
{
	public Collection $shared;
	public Collection $albums;
	public Collection $users;

	public function __construct(Collection $shared, Collection $albums, Collection $users)
	{
		$this->shared = $shared;
		$this->albums = $albums;
		$this->users = $users;
	}

	public function toArray(): array
	{
		return [
			'shared' => $this->shared->toArray(),
			'albums' => $this->albums->toArray(),
			'users' => $this->users->toArray(),
		];
	}
}
