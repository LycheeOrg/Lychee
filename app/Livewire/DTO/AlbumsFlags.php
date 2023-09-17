<?php

namespace App\Livewire\DTO;

use App\Contracts\Models\AbstractAlbum;
use App\Livewire\Traits\UseWireable;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Wireable;

class AlbumsFlags implements Wireable
{
	use UseWireable;

	public function __construct(
		public bool $can_edit = false,
		public bool $can_use_2fa = false
	) {
		$this->can_use_2fa = !Auth::check() && (WebAuthnCredential::query()->whereNull('disabled_at')->count() > 0);
		$this->can_edit = Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class]);
	}
}