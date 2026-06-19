<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Policies;

use App\Assets\Features;
use App\Enum\FacePermissionMode;
use App\Models\Person;
use App\Models\User;
use App\Repositories\ConfigManager;

/**
 * Authorization policy for Person and Face AI Vision operations.
 * Governed by the ai_vision_face_permission_mode configuration value.
 *
 * Permission matrix per mode:
 *
 * | Operation          | public              | private             | privacy-preserving        | restricted                |
 * |--------------------|---------------------|---------------------|---------------------------|---------------------------|
 * | View People page   | guest               | logged users        | admin only (*)            | admin only                |
 * | Create/edit Person | logged users        | logged users        | admin only (*)            | admin only                |
 * | Claim person       | logged users        | logged users        | logged users              | logged users              |
 * | Merge persons      | logged users        | logged users        | admin only (*)            | admin only                |
 * |                    |                     |                     |                           |                           |
 * | View face overlays | → PhotoPolicy::canViewFaceOverlays                                                          |
 * | Assign face        | → PhotoPolicy::canAssignFaceOnPhoto / AlbumPolicy::canAssignFaceInAlbum                    |
 * | Trigger scan       | → PhotoPolicy::canTriggerScanOnPhoto / AlbumPolicy::canTriggerScanOnAlbum                  |
 * | Dismiss face       | → PhotoPolicy::canDismissFace                                                               |
 * | Batch face ops     | → AlbumPolicy::canBatchFaceOps                                                              |
 * | View album people  | → AlbumPolicy::canViewAlbumPeople                                                           |
 *
 * (*) PRIVACY_PRESERVING returns false for non-admin in the global (entity-free) context.
 *     Per-entity ownership checks are enforced at the photo/album level in the scoped policies.
 */
class AiVisionPolicy extends BasePolicy
{
	public const CAN_VIEW_PEOPLE = 'canViewPeople';
	public const CAN_SHOW_PERSON = 'canShowPerson';
	public const CAN_EDIT_PERSON = 'canEditPerson';
	public const CAN_CLAIM_PERSON = 'canClaimPerson';
	public const CAN_MERGE_PERSONS = 'canMergePersons';
	public const CAN_CHANGE_PERSON_SEARCHABILITY = 'canChangePersonSearchability';

	/**
	 * Perform pre-authorization checks.
	 * If AI Vision feature is disabled, deny all access.
	 * Admins always pass if the feature is enabled.
	 *
	 * @param User|null $user
	 * @param string    $ability
	 *
	 * @return void|bool
	 */
	public function before(?User $user, $ability)
	{
		// If AI Vision feature is completely disabled, deny all access
		if (Features::inactive('ai-vision')) {
			return false;
		}

		// Admins bypass all other checks
		if ($user?->may_administrate === true) {
			return true;
		}
	}

	/**
	 * Get the current permission mode from configuration.
	 */
	private function getMode(): FacePermissionMode
	{
		return app(ConfigManager::class)->getValueAsEnum('ai_vision_face_permission_mode', FacePermissionMode::class) ?? FacePermissionMode::RESTRICTED;
	}

	/**
	 * View People page / list persons.
	 * public: guest;
	 * private: logged;
	 * privacy-preserving:
	 * owner+admin; restricted: admin only.
	 */
	public function canViewPeople(?User $user): bool
	{
		$mode = $this->getMode();

		return match ($mode) {
			FacePermissionMode::PUBLIC => true,
			FacePermissionMode::PRIVATE => $user !== null,
			FacePermissionMode::PRIVACY_PRESERVING => false, // admin handled by before()
			FacePermissionMode::RESTRICTED => false, // admin handled by before()
		};
	}

	/**
	 * View a specific Person record.
	 * Requires canViewPeople access, plus the person must be searchable or linked to the user.
	 * Admins always pass via before().
	 */
	public function canShowPerson(?User $user, Person $person): bool
	{
		if (!$this->canViewPeople($user)) {
			return false;
		}

		return $person->is_searchable || ($user !== null && $person->user_id === $user->id);
	}

	/**
	 * Create/edit Person.
	 * public: logged;
	 * private: logged;
	 * privacy-preserving: owner+admin;
	 * restricted: admin only.
	 */
	public function canEditPerson(?User $user): bool
	{
		$mode = $this->getMode();

		return match ($mode) {
			FacePermissionMode::PUBLIC => $user !== null,
			FacePermissionMode::PRIVATE => $user !== null,
			FacePermissionMode::PRIVACY_PRESERVING => false, // admin handled by before()
			FacePermissionMode::RESTRICTED => false, // admin handled by before()
		};
	}

	/**
	 * Claim a person (link to own user account).
	 * Requires the user to be logged in and user claims to be enabled in config.
	 * Admins always pass via before().
	 */
	public function canClaimPerson(?User $user): bool
	{
		if ($user === null) {
			return false;
		}

		return app(ConfigManager::class)->getValueAsBool('ai_vision_face_allow_user_claim');
	}

	/**
	 * Merge two persons.
	 * public: logged; private: logged; privacy-preserving: owner+admin; restricted: admin only.
	 */
	public function canMergePersons(?User $user): bool
	{
		// Same rules as edit
		return $this->canEditPerson($user);
	}

	/**
	 * Check if user can manage a specific person (delete, update searchability).
	 * Only the linked user or admin can manage.
	 */
	public function canManagePerson(?User $user, Person $person): bool
	{
		if ($user === null) {
			return false;
		}

		return $person->user_id === $user->id;
	}

	/**
	 * Change the is_searchable flag of a Person.
	 * Only the person's linked user or admin (via before()) may toggle it.
	 */
	public function canChangePersonSearchability(?User $user, Person $person): bool
	{
		return $person->user_id === $user?->id;
	}
}
