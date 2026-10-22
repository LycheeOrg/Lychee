<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SizeVariantAssetType;
use App\Enum\SizeVariantType;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\ResolvesSizeVariantAssetTrait;
use App\Models\Album;
use App\Models\PersonAlbum;
use App\Models\SizeVariant;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\RandomIDRule;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

/**
 * Request for GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant} (Feature 056).
 *
 * Resolves the SizeVariant and the album the caller claims to be viewing the
 * photo through, validates the optional temporary-link signature
 * (FR-056-04/05), then authorizes via {@link AlbumPolicy::CAN_ACCESS} on
 * that album plus {@link self::isPhotoOfAlbum()} confirming the photo is
 * actually part of it. `size_variant` is restricted to thumbnail-class
 * tokens (see {@link \App\Enum\SizeVariantAssetType}), so — unlike a plain
 * {@link \App\Policies\PhotoPolicy} check — there is no thumb-vs-full-photo
 * access split to make here.
 *
 * The caller-supplied album_id lets us resolve the album-level access check
 * through {@link \App\Factories\AlbumFactory::findAbstractAlbumOrFail()} and
 * {@link AlbumPolicy}, which already handle regular albums, tag albums,
 * person albums and smart albums uniformly. Photo-level membership
 * ({@link self::isPhotoOfAlbum()}) is then checked separately: for a real
 * {@link Album} via a raw `photo_album` pivot query, deliberately bypassing
 * the NSFW/visibility filtering that `Album::photos()`/`all_photos()` bake
 * in — that filtering would incorrectly hide a photo that has been set
 * (explicitly or automatically) as the album's cover.
 * {@link \App\Models\TagAlbum}/{@link \App\Models\PersonAlbum} get the same
 * cover exception (their own `cover_id` and/or the current viewer's cached
 * computed thumb in `album_user_thumbs`), but otherwise still fall back to
 * their own (filtered) `photos()` relation — reimplementing their
 * tag-match/person-match logic raw, to strip that filtering too, was out of
 * scope.
 *
 * This is a hot, high-frequency endpoint (one request per rendered image):
 * `photo_id` is taken straight from the validated route parameter with no
 * extra Photo lookup, and {@link self::sizeVariant()}'s query is narrowed to
 * only the columns {@link \App\Image\Watermarker::get_path()} and the
 * controller actually read. The album is loaded with its base record and
 * access permissions only, a regular album's membership is selected by the
 * size variant query itself, and admins skip the album entirely: they may
 * read every photo, so `album_id` is validated but never resolved for them
 * (Feature 102, FR-102-02).
 */
class GetPhotoAssetRequest extends BaseApiRequest
{
	use ResolvesSizeVariantAssetTrait;

	/** `null` for admins, who skip the album checks (see {@link self::skipsAlbumCheck()}). */
	private ?AbstractAlbum $album = null;
	private string $photo_id;
	private SizeVariant $size_variant;
	private SizeVariantType $size_variant_type;

	/**
	 * Set to true only when a required temporary-link signature is absent,
	 * malformed, invalid, expired, or in the future (FR-056-04's failure
	 * path) — i.e. when the caller failed to authenticate at all, as opposed
	 * to authenticating but being denied by PhotoPolicy.
	 * Drives {@link self::failedAuthorization()}'s 401-vs-403 choice
	 * (FR-056-05), since the inherited `Auth::check()`-based mechanism
	 * cannot express this endpoint's required mapping.
	 */
	public function sizeVariant(): SizeVariant
	{
		return $this->size_variant;
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var User|null $user */
		$user = Auth::user();

		if ($this->signatureRequired($user) && !$this->isSignatureValid()) {
			$this->signature_check_failed = true;

			return false;
		}

		// Admins: no album was loaded (see skipsAlbumCheck()).
		if ($this->album === null) {
			return true;
		}

		// The album must itself be accessible to the caller (owner, shared
		// permission, or public); membership is then checked separately,
		// deliberately bypassing the NSFW/visibility filtering that
		// Album::photos()/all_photos() bake in — that filtering answers a
		// different question ("should this photo surface in a listing?")
		// than the one we're asking here ("is this photo part of what
		// album_id legitimately represents?").
		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]) &&
			$this->isPhotoOfAlbum($this->album);
	}

	/**
	 * Whether `$this->photo_id` is part of `$album`, without any
	 * visibility/searchability filtering (see {@link self::authorize()}).
	 *
	 * For a regular {@link Album}, this also allows the photo through if it
	 * is that album's cover — hardcoded (`cover_id`) or automatically
	 * selected (`auto_cover_id_max_privilege`/`auto_cover_id_least_privilege`)
	 * — since a cover photo legitimately represents the album even when it
	 * physically lives in a descendant album, without needing to walk the
	 * `_lft`/`_rgt` subtree to find it. {@link TagAlbum} and
	 * {@link PersonAlbum} have no descendants, but the same cover exception
	 * applies: TagAlbum's own hardcoded `cover_id`, and — for both — the
	 * current viewer's cached computed thumb (`album_user_thumbs`, the
	 * tag/person equivalent of `auto_cover_id_*`; see
	 * {@link \App\Models\Extensions\CachesAlbumUserThumb}). {@link BaseSmartAlbum}
	 * gets the same cached-computed-thumb exception (2026-09-02 amendment,
	 * Feature 063 FR-056-08) — it has no hardcoded `cover_id` of its own, but
	 * its cover, when resolved by `GET /Albums/smart`,
	 * comes from this exact cache, and may have since fallen out of the
	 * smart album's own live `smart_photo_condition` (e.g. a photo was
	 * unstarred) before the next {@link \App\Jobs\RecomputeAlbumUserThumbsJob}
	 * run catches up — without this exception that cover would 403 here.
	 */
	private function isPhotoOfAlbum(AbstractAlbum $album): bool
	{
		if ($album instanceof Album) {
			if (in_array($this->photo_id, [
				$album->cover_id,
				$album->auto_cover_id_max_privilege,
				$album->auto_cover_id_least_privilege,
			], true)) {
				return true;
			}

			// Selected from `photo_album` by the size variant query (see
			// processValidatedValues()).
			return $this->size_variant->getAttribute(self::IS_IN_ALBUM_ATTRIBUTE) === true;
		}

		if ($album instanceof TagAlbum && $album->cover_id === $this->photo_id) {
			return true;
		}

		if (($album instanceof TagAlbum || $album instanceof PersonAlbum) && $this->isComputedAlbumThumb($album->id)) {
			return true;
		}

		if ($album instanceof BaseSmartAlbum && $this->isComputedAlbumThumb($album->get_id())) {
			return true;
		}

		return $album->photos()->whereKey($this->photo_id)->exists();
	}

	/**
	 * Whether `$this->photo_id` is the current viewer's cached computed
	 * thumb for `$album_id` — the tag/person-album equivalent of Album's
	 * `auto_cover_id_*` fields (see {@link \App\Models\AlbumUserThumb}).
	 * `Auth::id()` is `null` for a guest, matching the cache's convention
	 * for the public/guest view of the album.
	 */
	private function isComputedAlbumThumb(string $album_id): bool
	{
		return DB::table('album_user_thumbs')
			->where('album_id', $album_id)
			->where('user_id', Auth::id())
			->where('photo_id', $this->photo_id)
			->exists();
	}

	/**
	 * {@inheritDoc}
	 *
	 * The inherited {@link BaseApiRequest::failedAuthorization()} keys its
	 * 401-vs-403 choice off `Auth::check()` (session state), which is wrong
	 * here: a signature-valid guest denied by PhotoPolicy needs 403
	 * (S-056-03), while a logged-in user missing a config-required signature
	 * needs 401 (S-056-10) despite having a session.
	 */
	protected function failedAuthorization(): void
	{
		throw $this->signature_check_failed ? new UnauthenticatedException() : new UnauthorizedException();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			// AlbumIDRule, not RandomIDRule (2026-09-02 correction, Feature 063
			// FR-056-08): this endpoint's own docblock already claimed
			// AlbumFactory::findAbstractAlbumOrFail() "handle[s] regular albums,
			// tag albums, person albums and smart albums uniformly" — true for
			// resolution, but album_id validation was still RandomIDRule, which
			// only accepts RandomID::ID_LENGTH-character ids. Real Album/TagAlbum/
			// PersonAlbum ids all happen to be that length, so this went
			// unnoticed; a SmartAlbumType value (e.g. "unsorted") never is,
			// so every smart-album request 422'd before ever reaching
			// isPhotoOfAlbum() — discovered only once a real caller (this
			// feature's new BaseSmartAlbum branch) needed it to actually work.
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::SIZE_VARIANT_TOKEN_ATTRIBUTE => ['required', 'string', new Enum(SizeVariantAssetType::class)],
			self::MAC_ATTRIBUTE => ['nullable', 'string'],
		];
	}

	/**
	 * Merge route parameters and the temporary-link headers into request
	 * data for validation.
	 */
	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => $this->route(RequestAttribute::ALBUM_ID_ATTRIBUTE),
			RequestAttribute::PHOTO_ID_ATTRIBUTE => $this->route(RequestAttribute::PHOTO_ID_ATTRIBUTE),
			RequestAttribute::SIZE_VARIANT_TOKEN_ATTRIBUTE => $this->route(RequestAttribute::SIZE_VARIANT_TOKEN_ATTRIBUTE),
			self::MAC_ATTRIBUTE => $this->header(self::MAC_HEADER),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $album_id */
		$album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->photo_id = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		/** @var string $size_variant_token */
		$size_variant_token = $values[RequestAttribute::SIZE_VARIANT_TOKEN_ATTRIBUTE];

		if (!$this->skipsAlbumCheck(Auth::user())) {
			$this->album = $this->album_factory->findAbstractAlbumForAccessCheckOrFail($album_id);
		}
		$this->size_variant_type = SizeVariantAssetType::from($size_variant_token)->toSizeVariantType();

		$membership_album_id = $this->album instanceof Album ? $this->album->id : null;
		$size_variant = $this->resolveSizeVariant($this->photo_id, $this->size_variant_type, $membership_album_id);
		$this->size_variant_type = $size_variant->type;
		$this->size_variant = $size_variant;

		$this->mac = $values[self::MAC_ATTRIBUTE] ?? null;
	}

	/**
	 * Admins may read every photo ({@link \App\Policies\BasePolicy::before()}),
	 * so neither the album access nor the photo membership can deny them:
	 * the album is not loaded at all (Feature 102, FR-102-02).
	 */
	private function skipsAlbumCheck(?User $user): bool
	{
		return $user?->may_administrate === true;
	}
}
