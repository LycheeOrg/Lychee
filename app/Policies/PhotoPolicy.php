<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Policies;

use App\Assets\DbBool;
use App\Constants\AccessPermissionConstants as APC;
use App\Constants\PhotoAlbum as PA;
use App\Enum\FacePermissionMode;
use App\Enum\MetricsAccess;
use App\Enum\PhotoHighlightVisibilityType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PhotoPolicy extends BasePolicy
{
	public const CAN_SEE = 'canSee';
	public const CAN_DOWNLOAD = 'canDownload';
	public const CAN_EDIT = 'canEdit';
	public const CAN_MOVE = 'canMove';
	public const CAN_MOVE_ID = 'canMoveById';
	public const CAN_ACCESS_FULL_AND_DOWNLOAD_ID = 'canAccessFullAndDownloadById';
	public const CAN_EDIT_ID = 'canEditById';
	public const CAN_ACCESS_FULL_PHOTO = 'canAccessFullPhoto';
	public const CAN_DELETE_BY_ID = 'canDeleteById';
	public const CAN_READ_METRICS = 'canReadMetrics';
	public const CAN_READ_RATINGS = 'canReadRatings';
	public const CAN_HIGHLIGHT = 'canHighlight';
	public const CAN_VIEW_FACE_OVERLAYS = 'canViewFaceOverlays';
	public const CAN_DISMISS_FACE = 'canDismissFace';
	public const CAN_ASSIGN_FACE_ON_PHOTO = 'canAssignFaceOnPhoto';
	public const CAN_TRIGGER_SCAN_ON_PHOTO = 'canTriggerScanOnPhoto';

	/**
	 * @throws FrameworkException
	 */
	public function __construct(
		protected AlbumPolicy $album_policy,
	) {
	}

	/**
	 * This ensures that current photo is owned by current user.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	private function isOwner(?User $user, Photo $photo): bool
	{
		return $user !== null && $photo->owner_id === $user->id;
	}

	private function hasAlbums(Photo $photo): bool
	{
		return $photo->albums !== null && !$photo->albums->isEmpty();
	}

	/**
	 * Defines whether the photo is visible to the current user.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canSee(?User $user, Photo $photo): bool
	{
		if ($this->isOwner($user, $photo)) {
			return true;
		}

		return $this->hasAlbums($photo) && $this->reduction($photo->albums, fn ($a) => $this->album_policy->canAccess($user, $a));
	}

	/**
	 * Checks whether the photo may be downloaded by the current user.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canDownload(?User $user, Photo $photo): bool
	{
		if ($this->isOwner($user, $photo)) {
			return true;
		}

		if ($photo->is_validated !== true) {
			return false;
		}

		return $this->hasAlbums($photo) && $this->reduction($photo->albums, fn ($a) => $this->album_policy->canDownload($user, $a));
	}

	/**
	 * Checks whether the photo is editable by the current user.
	 *
	 * A photo is called _editable_ if the current user is allowed to edit
	 * the photo's properties.
	 * A photo is _editable_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the photo
	 *
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	public function canEdit(User $user, Photo $photo)
	{
		if ($photo->is_validated !== true) {
			return false;
		}
		if ($this->isOwner($user, $photo)) {
			return true;
		}

		return $this->hasAlbums($photo) && $this->reduction($photo->albums, fn ($a) => $this->album_policy->canEdit($user, $a));
	}

	/**
	 * Checks whether the photo may be moved or copied by the current user.
	 *
	 * A photo is movable if the user owns it and has the upload privilege,
	 * or if any album containing it is movable (see {@link AlbumPolicy::canMove()}).
	 *
	 * @param User  $user
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	public function canMove(User $user, Photo $photo): bool
	{
		if ($photo->is_validated !== true) {
			return false;
		}
		if ($this->isOwner($user, $photo) && $user->may_upload) {
			return true;
		}

		return $this->hasAlbums($photo) && $this->reduction($photo->albums, fn ($a) => $this->album_policy->canMove($user, $a));
	}

	/**
	 * Aggregate counterpart of {@link PhotoPolicy::canMove()} for a batch of
	 * photos: every photo is validated and either owned
	 * by the user (with the upload privilege) or contained in an album whose
	 * content the user may move (owned with the upload privilege, or a user or
	 * group permission granting move). Three queries, whatever the batch size.
	 *
	 * @param User     $user
	 * @param string[] $photo_ids
	 *
	 * @return bool
	 */
	public function canMoveById(User $user, array $photo_ids): bool
	{
		$photo_ids = array_values(array_unique($photo_ids));
		$rows = Photo::query()->whereIn('id', $photo_ids)->toBase()->get(['id', 'owner_id', 'is_validated']);

		if ($rows->count() !== count($photo_ids) || $rows->contains(fn ($row) => !DbBool::parse($row->is_validated))) {
			return false;
		}

		/** @var string[] $foreign_ids */
		$foreign_ids = $rows
			->filter(fn ($row) => !($user->may_upload && (int) $row->owner_id === $user->id))
			->pluck('id')->all();

		return $foreign_ids === [] ||
			$this->countPhotosWithAlbumGrant($user, $foreign_ids, APC::GRANTS_MOVE, owned_album_counts: $user->may_upload, public_counts: false) === count($foreign_ids);
	}

	/**
	 * Aggregate counterpart of {@link PhotoPolicy::canAccessFullPhoto()} &&
	 * {@link PhotoPolicy::canDownload()} for a batch of photos (cross-owner
	 * guard): every photo is owned by the user, or has a
	 * containing album granting full-photo access and one granting download
	 * (album owned by the user, or a user, group or public permission).
	 * Three queries, whatever the batch size.
	 *
	 * @param User     $user
	 * @param string[] $photo_ids
	 *
	 * @return bool
	 */
	public function canAccessFullAndDownloadById(User $user, array $photo_ids): bool
	{
		$photo_ids = array_values(array_unique($photo_ids));

		/** @var string[] $foreign_ids */
		$foreign_ids = Photo::query()
			->whereIn('id', $photo_ids)
			->where('owner_id', '!=', $user->id)
			->pluck('id')->all();

		if ($foreign_ids === []) {
			return true;
		}

		$count = count($foreign_ids);

		return $this->countPhotosWithAlbumGrant($user, $foreign_ids, APC::GRANTS_FULL_PHOTO_ACCESS, owned_album_counts: true, public_counts: true) === $count &&
			$this->countPhotosWithAlbumGrant($user, $foreign_ids, APC::GRANTS_DOWNLOAD, owned_album_counts: true, public_counts: true) === $count;
	}

	/**
	 * Number of the designated photos contained in at least one album that the
	 * user owns (when $owned_album_counts) or on which a user or group (or public,
	 * when $public_counts) permission carries $grant.
	 *
	 * @param string[] $photo_ids
	 */
	private function countPhotosWithAlbumGrant(User $user, array $photo_ids, string $grant, bool $owned_album_counts, bool $public_counts): int
	{
		/** @var int[] $group_ids */
		$group_ids = $user->user_groups->pluck('id')->all();

		return DB::table(PA::PHOTO_ALBUM)
			->join('base_albums', 'base_albums.id', '=', PA::ALBUM_ID)
			->whereIn(PA::PHOTO_ID, $photo_ids)
			->where(fn ($q) => $q
				->when($owned_album_counts, fn ($q1) => $q1->orWhere('base_albums.owner_id', '=', $user->id))
				->orWhereExists(fn ($q2) => $q2
					->from(APC::ACCESS_PERMISSIONS, 'grant_perm')
					->selectRaw('1')
					->whereColumn('grant_perm.' . APC::BASE_ALBUM_ID, '=', PA::ALBUM_ID)
					->where('grant_perm.' . $grant, '=', true)
					->where(fn ($q3) => $q3
						->where('grant_perm.' . APC::USER_ID, '=', $user->id)
						->orWhereIn('grant_perm.' . APC::USER_GROUP_ID, $group_ids)
						->when($public_counts, fn ($q4) => $q4->orWhere(fn ($q5) => $q5->whereNull('grant_perm.' . APC::USER_ID)->whereNull('grant_perm.' . APC::USER_GROUP_ID)))
					)
				)
			)
			->distinct()
			->count(PA::PHOTO_ID);
	}

	/**
	 * Checks whether the designated photos are editable by the current user.
	 *
	 * @param User     $user
	 * @param string[] $photo_ids
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function canEditById(User $user, array $photo_ids): bool
	{
		// Make IDs unique as otherwise count will fail.
		$photo_ids = array_unique($photo_ids);

		// If there are any photos which are not validated at this point, we fail.
		if (
			Photo::query()
			->whereIn('id', $photo_ids)
			->where('is_validated', false)
			->count() > 0
		) {
			return false;
		}

		if (
			$user->may_upload &&
			Photo::query()
			->whereIn('id', $photo_ids)
			->where('owner_id', $user->id)
			->count() === count($photo_ids)
		) {
			return true;
		}

		// Unsorted photos are only editable by the owner or admin.
		// This is checked by the query above.
		// If any of the photos are unsorted at this point, we fail.
		// Better safe than sorry.
		if (
			Photo::query()
			->leftJoin(PA::PHOTO_ALBUM, 'photos.id', '=', PA::PHOTO_ID)
			->whereNull('album_id')
			->whereIn('photos.id', $photo_ids)
			->count() > 0
		) {
			return false;
		}

		$parents_id = DB::table(PA::PHOTO_ALBUM)
			->select(PA::ALBUM_ID)
			->whereIn(PA::PHOTO_ID, $photo_ids)
			->groupBy('album_id')
			->pluck('album_id')->all();

		return $this->album_policy->canEditById($user, $parents_id);
	}

	/**
	 * Checks whether the photo may be seen full resolution by the current user.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function canAccessFullPhoto(?User $user, Photo $photo): bool
	{
		if ($this->isOwner($user, $photo)) {
			return true;
		}

		if (!$this->canSee($user, $photo)) {
			return false;
		}

		return $this->hasAlbums($photo) && $this->reduction($photo->albums, fn ($a) => $this->album_policy->canAccessFullPhoto($user, $a));
	}

	/**
	 * Checks whether the designated photos are deletable by the current user.
	 *
	 * @param User     $user
	 * @param string[] $photo_ids
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function canDeleteById(User $user, array $photo_ids): bool
	{
		// Make IDs unique as otherwise count will fail.
		$photo_ids = array_unique($photo_ids);

		if (
			$user->may_upload &&
			Photo::query()
			->whereIn('id', $photo_ids)
			->where('owner_id', $user->id)
			->count() === count($photo_ids)
		) {
			return true;
		}

		// If there are any photos which are not in albums at this point, we fail.
		if (
			Photo::query()
			->leftJoin(PA::PHOTO_ALBUM, 'photos.id', '=', PA::PHOTO_ID)
			->whereNull('album_id')
			->whereIn('photos.id', $photo_ids)
			->count() > 0
		) {
			return false;
		}

		$parent_ids = DB::table(PA::PHOTO_ALBUM)
			->select(PA::ALBUM_ID)
			->whereIn(PA::PHOTO_ID, $photo_ids)
			->groupBy(PA::ALBUM_ID)
			->pluck('album_id')->all();

		// A photo is content of its albums: the delete grant is checked on them.
		return $this->album_policy->canDeleteContentById($user, $parent_ids);
	}

	/**
	 * Check whether the user can read the metrics of the photo.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canReadMetrics(?User $user, Photo $photo): bool
	{
		$config_manager = app(ConfigManager::class);
		$access_level = $config_manager->getValueAsEnum('metrics_access', MetricsAccess::class);

		return match ($access_level) {
			MetricsAccess::PUBLIC => true,
			MetricsAccess::LOGGED_IN => $user !== null,
			MetricsAccess::OWNER => $user !== null && $photo->owner_id === $user->id,
			MetricsAccess::ADMIN => $user?->may_administrate === true,
			default => false,
		};
	}

	/**
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canReadRatings(?User $user, Photo $photo): bool
	{
		$config_manager = app(ConfigManager::class);
		// Rating are disabled globally
		if (!$config_manager->getValueAsBool('rating_enabled')) {
			return false;
		}

		// Note that this will bypass the setting 'rating_show_only_when_user_rated'
		// It is up to the admin to decide whether anonymous users can see ratings at all.
		return ($user !== null) || $config_manager->getValueAsBool('rating_public');
	}

	/**
	 * Checks whether the photo can be highlighted by the current user.
	 *
	 * A photo is called _highlighted_ if the current user is allowed to star
	 * the photo.
	 * A photo can be _highlighted_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 * - the settings is set to allow anonymous users to star photos
	 * - the settings is set to allow authenticated users to star photos and the user is authenticated
	 * - the settings is set to allow editors to star photos and the user is an editor
	 * - the user is the owner of the photo (checked via canEdit method)
	 * - the user is admin (checked by before method)
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canHighlight(?User $user, Photo $photo): bool
	{
		$config_manager = app(ConfigManager::class);
		$visibility = $config_manager->getValueAsEnum('photos_star_visibility', PhotoHighlightVisibilityType::class);

		return match ($visibility) {
			PhotoHighlightVisibilityType::ANONYMOUS => $this->canSee($user, $photo),
			PhotoHighlightVisibilityType::AUTHENTICATED => $user !== null && $this->canSee($user, $photo),
			PhotoHighlightVisibilityType::EDITOR => $user !== null && $this->canEdit($user, $photo),
			default => false,
		};
	}

	/**
	 * @param Collection<int,Album>        $albums
	 * @param \Closure(Album $album): bool $reducer
	 *
	 * @return bool
	 */
	private function reduction(Collection $albums, \Closure $reducer): bool
	{
		return $albums->reduce(
			fn (bool $carry, Album $album) => $carry || $reducer($album),
			false
		);
	}

	// ── AI Vision / Face gates ────────────────────────────────────────────

	/**
	 * Resolve the current FacePermissionMode from configuration.
	 */
	private function getFaceMode(): FacePermissionMode
	{
		return app(ConfigManager::class)->getValueAsEnum('ai_vision_face_permission_mode', FacePermissionMode::class) ?? FacePermissionMode::RESTRICTED;
	}

	/**
	 * Return false if AI Vision is not enabled (ai_vision_enabled + ai_vision_face_enabled).
	 * Admin bypass is already handled by BasePolicy::before().
	 */
	private function isFaceEnabled(): bool
	{
		$cfg = app(ConfigManager::class);

		return $cfg->getValueAsBool('ai_vision_enabled') && $cfg->getValueAsBool('ai_vision_face_enabled');
	}

	/**
	 * Check whether the current user may see face overlays on this photo.
	 *
	 * Permission matrix (admin handled by before()):
	 *   public              → album access (canSee)
	 *   private             → logged-in user
	 *   privacy-preserving  → photo owner or album editor
	 *   restricted          → photo owner or album editor
	 */
	public function canViewFaceOverlays(?User $user, Photo $photo): bool
	{
		if (!$this->isFaceEnabled()) {
			return false;
		}

		return match ($this->getFaceMode()) {
			FacePermissionMode::PUBLIC => $this->canSee($user, $photo),
			FacePermissionMode::PRIVATE => $user !== null,
			FacePermissionMode::PRIVACY_PRESERVING,
			FacePermissionMode::RESTRICTED => $user !== null && $this->canEdit($user, $photo),
		};
	}

	/**
	 * Check whether the current user may dismiss a face on this photo.
	 *
	 * Dismiss is always restricted to the photo owner regardless of mode.
	 * Admin bypass is handled by before().
	 */
	public function canDismissFace(?User $user, Photo $photo): bool
	{
		if (!$this->isFaceEnabled()) {
			return false;
		}

		return $this->isOwner($user, $photo);
	}

	/**
	 * Check whether the current user may assign a face on this photo to a person.
	 *
	 * Permission matrix (admin handled by before()):
	 *   public              → logged-in user
	 *   private             → logged-in user
	 *   privacy-preserving  → photo owner or album editor
	 *   restricted          → admin only
	 */
	public function canAssignFaceOnPhoto(?User $user, Photo $photo): bool
	{
		if (!$this->isFaceEnabled()) {
			return false;
		}

		return match ($this->getFaceMode()) {
			FacePermissionMode::PUBLIC => $user !== null,
			FacePermissionMode::PRIVATE => $user !== null,
			FacePermissionMode::PRIVACY_PRESERVING => $user !== null && $this->canEdit($user, $photo),
			FacePermissionMode::RESTRICTED => false,
		};
	}

	/**
	 * Check whether the current user may trigger a face scan on this photo.
	 *
	 * Permission matrix (admin handled by before()):
	 *   public              → logged-in user
	 *   private             → logged-in user
	 *   privacy-preserving  → photo owner or album editor
	 *   restricted          → photo owner or album editor
	 */
	public function canTriggerScanOnPhoto(?User $user, Photo $photo): bool
	{
		if (!$this->isFaceEnabled()) {
			return false;
		}

		return match ($this->getFaceMode()) {
			FacePermissionMode::PUBLIC => $user !== null,
			FacePermissionMode::PRIVATE => $user !== null,
			FacePermissionMode::PRIVACY_PRESERVING,
			FacePermissionMode::RESTRICTED => $user !== null && $this->canEdit($user, $photo),
		};
	}
}
