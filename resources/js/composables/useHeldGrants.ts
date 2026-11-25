/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed } from "vue";
import { useGlobalRights } from "@/composables/useGlobalRights";

/**
 * Which grants the current user may hand out when sharing (Feature 118, FR-118-07):
 * upload needs their upload right, edit/move/delete their edit right.
 * The root album rights carry both (admins: always true).
 *
 * A checkbox for a lacked grant is disabled only while unticked, so a grant
 * given before the right was withdrawn can still be removed.
 */
export function useHeldGrants() {
	const { rights } = useGlobalRights();

	const canGrantUpload = computed(() => rights.value?.root_album.can_upload ?? false);
	const canGrantEdit = computed(() => rights.value?.root_album.can_edit ?? false);

	function lockUpload(current: boolean): boolean {
		return !canGrantUpload.value && !current;
	}

	function lockEdit(current: boolean): boolean {
		return !canGrantEdit.value && !current;
	}

	return { canGrantUpload, canGrantEdit, lockUpload, lockEdit };
}
