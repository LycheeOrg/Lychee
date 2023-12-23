<?php

namespace Database\Factories\Traits;

use App\Models\User;

trait OwnedBy
{
	abstract public function state($state);

	/**
	 * Defines the owner of the create albums.
	 *
	 * @param User $user
	 *
	 * @return self
	 */
	public function owned_by(User $user): self
	{
		return $this->state(function (array $attributes) use ($user) {
			return [
				'owner_id' => $user->id,
			];
		});
	}
}