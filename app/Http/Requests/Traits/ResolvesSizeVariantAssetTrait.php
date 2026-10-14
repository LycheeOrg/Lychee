<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Enum\SizeVariantType;
use App\Models\SizeVariant;
use App\Models\User;
use App\Services\TemporaryLinkSigner;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Shared by the binary image routes `GET /api/v3/Asset/...` (Feature 056)
 * and `GET /api/v3/Faces/{face_id}/photo/{variant}` (Feature 093,
 * ADR-093-01): the temporary-link signature check (ADR-0008) and the size
 * variant fallback walk.
 */
trait ResolvesSizeVariantAssetTrait
{
	protected const MAC_HEADER = 'X-Mac';

	protected const MAC_ATTRIBUTE = 'temporary_link_mac';

	protected ?string $mac = null;

	protected bool $signature_check_failed = false;

	/**
	 * Resolve the size variant row of `$photo_id` for `$type`.
	 *
	 * The requested type may simply never have been generated for this
	 * photo (e.g. a `small2x` variant when the source is too small to
	 * warrant a 2x thumbnail) - walk the same fallback chain
	 * PhotoAssetController::fallback() uses for a missing *file*
	 * (SizeVariantType::fallbackType()) so a missing DB *row* degrades
	 * the same way instead of 404ing outright. The controller then
	 * continues that exact chain from whichever type we resolve to here
	 * if that type's file also turns out to be missing on disk.
	 *
	 * @throws ModelNotFoundException
	 */
	protected function resolveSizeVariant(string $photo_id, SizeVariantType $type): SizeVariant
	{
		$size_variant = null;
		while ($size_variant === null) {
			// SizeVariant must stay a real model: Watermarker::get_path() and
			// the controller rely on its enum casts (type, storage_disk). We
			// still limit the hydrated columns to only what those two call
			// sites read.
			$size_variant = SizeVariant::query()
				->select(['id', 'photo_id', 'type', 'short_path', 'short_path_watermarked', 'storage_disk'])
				->where('photo_id', '=', $photo_id)
				->where('type', '=', $type)
				->first();

			if ($size_variant !== null) {
				break;
			}

			$next = $type->fallbackType();
			if ($next === null) {
				throw (new ModelNotFoundException())->setModel(SizeVariant::class);
			}
			$type = $next;
		}

		return $size_variant;
	}

	/**
	 * Determines whether `$user` must additionally present a valid
	 * temporary-link signature to be authorized (ADR-0008).
	 *
	 * `false` for every caller — guests included — when
	 * `temporary_image_link_enabled` is off (Q-056-05): disabling the
	 * feature drops the extra signature requirement outright rather than
	 * locking guests out, so a disabled-feature guest relies solely on the
	 * ordinary `AlbumPolicy`/`PhotoPolicy` check like any other caller.
	 * Otherwise, guests are only ever authorized via a valid temporary link
	 * (FR-056-05) — there's no session to fall back on. For authenticated
	 * users, this mirrors
	 * {@link \App\Services\UrlGenerator::shouldNotUseSignedUrl()}'s
	 * generation-time predicate, re-purposed for validation.
	 */
	protected function signatureRequired(?User $user): bool
	{
		if (!$this->configs()->getValueAsBool('temporary_image_link_enabled')) {
			return false;
		}

		if ($user === null) {
			return true;
		}

		if ($user->may_administrate) {
			return $this->configs()->getValueAsBool('temporary_image_link_when_admin');
		}

		return $this->configs()->getValueAsBool('temporary_image_link_when_logged_in');
	}

	/**
	 * Validates the temporary-link signature: the feature must be globally
	 * enabled, the header must be present (FR-056-04), and the code must
	 * verify against the current time step (or its one-step grace window —
	 * see {@link TemporaryLinkSigner}), which is what bounds its freshness;
	 * there is no separate timestamp/TTL check to make here any more.
	 */
	public function isSignatureValid(): bool
	{
		if (!$this->configs()->getValueAsBool('temporary_image_link_enabled')) {
			return false;
		}

		if ($this->mac === null) {
			return false;
		}

		return (new TemporaryLinkSigner())->verify($this->mac);
	}
}
