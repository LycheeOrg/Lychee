<?php

namespace App\Database\Query;

use Illuminate\Database\Query\Builder;

class NullBuilder extends Builder
{
	public static function createFromQueryBuilder(Builder $query): self
	{
		return new self($query->getConnection(), $query->getGrammar(), $query->getProcessor());
	}

	protected function runSelect(): array
	{
		return [];
	}

	public function update(array $values): int
	{
		return 0;
	}

	public function updateFrom(array $values): int
	{
		return 0;
	}

	public function insert(array $values): bool
	{
		return false;
	}

	public function delete($id = null): int
	{
		return 0;
	}
}
