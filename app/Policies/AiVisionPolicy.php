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
 * | View People page   | guest               | logged users        | photo/album owner + admin | admin only                |
 * | View face overlays | album access        | logged users        | photo/album owner + admin | photo/album owner + admin |
 * | Create/edit Person | logged users        | logged users        | photo/album owner + admin | admin only                |
 * | Assign face        | logged users        | logged users        | photo/album owner + admin | admin only                |
 * | Trigger scan       | logged users        | logged users        | photo/album owner + admin | photo/album owner + admin |
 * | Claim person       | logged users        | logged users        | logged users              | logged users              |
 * | Merge persons      | logged users        | logged users        | photo/album owner + admin | admin only                |
 * | Dismiss face       | photo owner + admin | photo owner + admin | photo owner + admin       | photo owner + admin       |
 * | Batch face ops     | logged users        | logged users        | photo/album owner + admin | admin only                |
 * | View album people  | album access        | logged users        | photo/album owner + admin | photo/album owner + admin |
 */
class AiVisionPolicy extends BasePolicy
{
	public const CAN_VIEW_PEOPLE = 'canViewPeople';
	public const CAN_SHOW_PERSON = 'canShowPerson';
	public const CAN_EDIT_PERSON = 'canEditPerson';
	public const CAN_ASSIGN_FACE = 'canAssignFace';
	public const CAN_TRIGGER_SCAN = 'canTriggerScan';
	public const CAN_CLAIM_PERSON = 'canClaimPerson';
	public const CAN_MERGE_PERSONS = 'canMergePersons';
	public const CAN_DISMISS_FACE = 'canDismissFace';
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
	 * public: guest; private: logged; privacy-preserving: owner+admin; restricted: admin only.
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

		return $person->is_searchable || $person->user_id === $user?->id;
	}

	/**
	 * Create/edit Person.
	 * public: logged; private: logged; privacy-preserving: owner+admin; restricted: admin only.
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
	 * Assign face to person.
	 * public: logged; private: logged; privacy-preserving: owner+admin; restricted: admin only.
	 */
	public function canAssignFace(?User $user): bool
	{
		// Same rules as edit
		return $this->canEditPerson($user);
	}

	/**
	 * Trigger face scan on photos.
	 * public: logged; private: logged; privacy-preserving: owner+admin; restricted: owner+admin.
	 */
	public function canTriggerScan(?User $user): bool
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
	 * Dismiss / undismiss a face.
	 * photo owner or admin (handled at controller level; this gate is for admin check only).
	 */
	public function canDismissFace(?User $user): bool
	{
		return $user !== null;
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
