<?php

declare(strict_types=1);

namespace App\Livewire\DTO;

use App\Exceptions\Internal\LycheeLogicException;
use Illuminate\Support\Collection;

/**
 * @template T
 */
class ProtectedCollection
{
	/**
	 * @param bool                   $is_loaded
	 * @param Collection<int,T>|null $collection
	 *
	 * @return void
	 */
	public function __construct(
		private string $type,
		private bool $is_loaded = false,
		private ?Collection $collection = null,
	) {
	}

	/**
	 * @return Collection<int,T>|null collection encapsulated
	 *
	 * @throws LycheeLogicException we queried it too early
	 */
	public function get(): Collection|null
	{
		if ($this->is_loaded) {
			return $this->collection;
		}

		throw new LycheeLogicException(sprintf('Collection %s data is not available.', $this->type));
	}

	/**
	 * Set the collection.
	 *
	 * @param Collection<int,T>|null $collection
	 *
	 * @return void
	 */
	public function set(Collection|null $collection)
	{
		$this->is_loaded = true;
		$this->collection = $collection;
	}
}