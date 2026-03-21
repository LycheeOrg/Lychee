<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Policies;

use App\Enum\FacePermissionMode;
use App\Models\Person;
use App\Models\User;
use App\Repositories\ConfigManager;

/**
 * Authorization policy for Person and Face AI Vision operations.
 * Governed by the ai_vision_face_permission_mode configuration value.
 *
 * Permission matrix per mode:
 * | Operation          | public       | private      | privacy-preserving        | restricted                |
 * |--------------------|--------------|--------------|---------------------------|---------------------------|
 * | View People page   | guest        | logged users | photo/album owner + admin | admin only                |
 * | View face overlays | album access | logged users | photo/album owner + admin | photo/album owner + admin |
 * | Create/edit Person | logged users | logged users | photo/album owner + admin | admin only                |
 * | Assign face        | logged users | logged users | photo/album owner + admin | admin only                |
 * | Trigger scan       | logged users | logged users | photo/album owner + admin | photo/album owner + admin |
 * | Claim person       | logged users | logged users | logged users              | logged users              |
 * | Merge persons      | logged users | logged users | photo/album owner + admin | admin only                |
 */
class AiVisionPolicy extends BasePolicy
{
	public const CAN_VIEW_PEOPLE = 'canViewPeople';
	public const CAN_EDIT_PERSON = 'canEditPerson';
	public const CAN_ASSIGN_FACE = 'canAssignFace';
	public const CAN_TRIGGER_SCAN = 'canTriggerScan';
	public const CAN_CLAIM_PERSON = 'canClaimPerson';
	public const CAN_MERGE_PERSONS = 'canMergePersons';
	public const CAN_DISMISS_FACE = 'canDismissFace';

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
	 * All modes: logged users (admins always pass via before()).
	 */
	public function canClaimPerson(?User $user): bool
	{
		return $user !== null;
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
}
