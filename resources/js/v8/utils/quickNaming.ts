/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * Pure helpers for the face quick naming view (Feature 093).
 * No Vue, no I/O: adapters from the v3 Struct-of-Arrays payloads, name matching,
 * suggestion resolution and the keyboard decision table.
 */

export type QueueFace = {
	id: string;
	photoId: string;
	x: number;
	y: number;
	width: number;
	height: number;
	cropUrl: string | null;
	clusterLabel: number | null;
	suggestedPersonId: string | null;
	suggestedPersonName: string | null;
	/** Sharpness of the face crop (FR-093-15). */
	laplacianVariance: number;
};

export type PersonOption = {
	id: string;
	name: string;
	faceCount: number;
	cropUrl: string | null;
	/** Created in this session, server id not known yet (FR-093-13). */
	isPending: boolean;
};

export type MatchEntry = { kind: "person"; person: PersonOption } | { kind: "create"; name: string; another: boolean };

export type Suggestion = { personId: string; name: string };

export type KeyAction = "none" | "submit" | "create" | "skip" | "back" | "discard" | "clear" | "highlight-next" | "highlight-prev" | "leave";

export const MAX_MATCHES = 8;

export function adaptFaceQueue(soa: App.Http.Resources.V3.FaceQueueResource): QueueFace[] {
	return soa.ids.map((id, i) => ({
		id,
		photoId: soa.photo_ids[i],
		x: soa.xs[i],
		y: soa.ys[i],
		width: soa.widths[i],
		height: soa.heights[i],
		cropUrl: soa.crop_urls[i],
		clusterLabel: soa.cluster_labels[i],
		suggestedPersonId: soa.suggested_person_ids[i],
		suggestedPersonName: soa.suggested_person_names[i],
		laplacianVariance: soa.laplacian_variances[i],
	}));
}

export function adaptPeopleList(soa: App.Http.Resources.V3.PeopleListResource): PersonOption[] {
	return soa.ids.map((id, i) => ({
		id,
		name: soa.names[i],
		faceCount: soa.face_counts[i],
		cropUrl: soa.crop_urls[i],
		isPending: false,
	}));
}

function byNameThenFaces(a: PersonOption, b: PersonOption): number {
	return a.name.localeCompare(b.name) || b.faceCount - a.faceCount;
}

/**
 * Ranked entries for the typed text (FR-093-03): exact matches (suggested person first, then by
 * face count), then prefix matches, then substring matches; at most {@link MAX_MATCHES} persons,
 * always followed by a create entry ("another" when the name already exists, homonyms allowed).
 */
export function rankMatches(people: PersonOption[], text: string, suggestedPersonId: string | null): MatchEntry[] {
	const name = text.trim();
	if (name === "") {
		return [];
	}
	const needle = name.toLocaleLowerCase();
	const lower = (p: PersonOption) => p.name.toLocaleLowerCase();

	const exact = people
		.filter((p) => lower(p) === needle)
		.sort((a, b) => Number(b.id === suggestedPersonId) - Number(a.id === suggestedPersonId) || b.faceCount - a.faceCount);
	const prefix = people.filter((p) => lower(p) !== needle && lower(p).startsWith(needle)).sort(byNameThenFaces);
	const substring = people.filter((p) => !lower(p).startsWith(needle) && lower(p).includes(needle)).sort(byNameThenFaces);

	const persons: MatchEntry[] = [...exact, ...prefix, ...substring].slice(0, MAX_MATCHES).map((person) => ({ kind: "person", person }));

	return [...persons, { kind: "create", name, another: exact.length > 0 }];
}

/**
 * Suggestion for `face` (FR-093-12): the person just given to the previous face of the same
 * cluster wins over the server suggestion.
 */
export function resolveSuggestion(face: QueueFace, previousFace: QueueFace | null, previousNamedPerson: Suggestion | null): Suggestion | null {
	const sameCluster =
		previousFace !== null && face.clusterLabel !== null && face.clusterLabel >= 0 && previousFace.clusterLabel === face.clusterLabel;
	if (sameCluster && previousNamedPerson !== null) {
		return previousNamedPerson;
	}
	if (face.suggestedPersonId !== null && face.suggestedPersonName !== null) {
		return { personId: face.suggestedPersonId, name: face.suggestedPersonName };
	}

	return null;
}

export type KeyInput = { key: string; shiftKey: boolean; repeat: boolean; isComposing: boolean };

export type KeyState = {
	/** Current text of the name box. */
	text: string;
	/** Pre-filled name of a revisited named face, else null. */
	prefill: string | null;
	/** Whether the match list is showing entries. */
	hasEntries: boolean;
	isRtl: boolean;
};

/**
 * Keyboard decision table (FR-093-03, FR-093-04, FR-093-06): returns what a key press means;
 * "none" lets the browser handle it (typing, caret movement).
 */
export function decideKey(input: KeyInput, state: KeyState): KeyAction {
	if (input.isComposing) {
		return "none";
	}

	const isEmpty = state.text === "";
	const isUnchangedPrefill = state.prefill !== null && state.prefill !== "" && state.text === state.prefill;

	switch (input.key) {
		case "Enter":
			if (input.shiftKey) {
				return state.text.trim() === "" ? "none" : "create";
			}
			return "submit";
		case "ArrowDown":
			return state.hasEntries ? "highlight-next" : "none";
		case "ArrowUp":
			return state.hasEntries ? "highlight-prev" : "none";
		case "ArrowRight":
		case "ArrowLeft": {
			if (!isEmpty && !isUnchangedPrefill) {
				return "none";
			}
			const forward = state.isRtl ? "ArrowLeft" : "ArrowRight";
			return input.key === forward ? "skip" : "back";
		}
		case "Backspace":
			if (isUnchangedPrefill) {
				return "clear";
			}
			return isEmpty && !input.repeat ? "discard" : "none";
		case " ":
			return isEmpty && !input.repeat ? "discard" : "none";
		case "Escape":
			return "leave";
		default:
			return "none";
	}
}
