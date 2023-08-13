<?php

namespace App\Livewire\Forms;

use App\Livewire\DTO\PermissionsFlags;
use App\Models\AccessPermission;
use App\Models\Extensions\BaseAlbum;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Form;

class SharingAlbumForms extends Form
{
	#[Locked]
	public BaseAlbum $album;

	/** @var array<int,PermissionsFlags> */
	public array $values = [];

	/**
	 * This allows Livewire to know which values of the $configs we
	 * want to display in the wire:model. Sort of a white listing.
	 *
	 * @var array<string,string>
	 */
	protected $rules = [
		'values.*.' => 'nullable',
	];

	/**
	 * @param BaseAlbum $baseAlbum
	 *
	 * @return void
	 */
	public function setAlbum(BaseAlbum $baseAlbum)
	{
		$this->album = $baseAlbum;
	}

	/**
	 * Initialize form data.
	 *
	 * @param Collection<AccessPermission> $configs
	 *
	 * @return void
	 */
	public function setSharing(Collection $perms): void
	{
		$perms->each(function (AccessPermission $p) {
			$this->values[] = new PermissionsFlags(
				$p->user_id,
				$p->user->username,
				$p->grants_full_photo_access,
				$p->grants_download,
				$p->grants_upload,
				$p->grants_edit,
				$p->grants_delete
			);
		});
		// $this->configs = $configs;
		// $this->values = $configs->map(fn (Configs $c, int $k) => $c->value)->all();
	}

	/**
	 * Save form data.
	 *
	 * @return void
	 *
	 * @throws ValidationException
	 */
	public function save(): void
	{
		// $this->validate();
	}
}