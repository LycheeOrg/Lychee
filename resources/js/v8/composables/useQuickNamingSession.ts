/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, reactive, ref } from "vue";
import FaceQuickNamingService from "@/services/face-quick-naming-service";
import { adaptFaceQueue, adaptPeopleList, resolveSuggestion, type PersonOption, type QueueFace, type Suggestion } from "@/v8/utils/quickNaming";

export type EntryStatus = "open" | "named" | "skipped" | "discarded";

export type SessionEntry = {
	face: QueueFace;
	status: EntryStatus;
	/** Optimistic assignment shown to the user. */
	person: { id: string; name: string } | null;
	/** A discard was sent at least once; un-dismiss before assigning (FR-093-06). */
	mayBeDismissed: boolean;
	/** Last request failed (FR-093-09). */
	failed: boolean;
	/** Text kept in the box after a failure. */
	failedText: string;
};

/** How many photos ahead are preloaded (NFR-093-01). */
const PRELOAD_AHEAD = 3;
/** Photos further behind than this are released. */
const KEEP_BEHIND = 20;

/**
 * State of one quick naming session (Feature 093): the queue, the cursor, the optimistic actions
 * and the request bookkeeping. The session lives in memory until the view is left (FR-093-11).
 */
export function useQuickNamingSession(clusterLabel: number | null, onFailure: (index: number) => void) {
	const entries = ref<SessionEntry[]>([]);
	const people = ref<PersonOption[]>([]);
	const index = ref(0);
	const isTruncated = ref(false);
	const isLoading = ref(false);
	const loadError = ref(false);
	const isRefetching = ref(false);

	const photoUrls = reactive(new Map<string, string>());
	const photoRequests = new Map<string, AbortController>();

	// Per-face request chains keep requests of one face in order (FR-093-13).
	const faceChains = new Map<string, Promise<void>>();
	// Pending person creations: local id → promise of the server id (FR-093-13).
	const pendingCreations = new Map<string, Promise<string>>();
	let pendingCounter = 0;

	const current = computed<SessionEntry | null>(() => entries.value[index.value] ?? null);
	const isDone = computed(() => !isLoading.value && !loadError.value && current.value === null && !isRefetching.value);

	const suggestion = computed<Suggestion | null>(() => {
		const entry = current.value;
		if (entry === null || entry.status === "named") {
			return null;
		}
		const previous = entries.value[index.value - 1] ?? null;
		const previousNamed =
			previous?.status === "named" && previous.person !== null ? { personId: previous.person.id, name: previous.person.name } : null;
		return resolveSuggestion(entry.face, previous?.face ?? null, previousNamed);
	});

	function toEntry(face: QueueFace): SessionEntry {
		return { face, status: "open", person: null, mayBeDismissed: false, failed: false, failedText: "" };
	}

	async function load(): Promise<void> {
		isLoading.value = true;
		loadError.value = false;
		try {
			const [queue, list] = await Promise.all([FaceQuickNamingService.getQueue(clusterLabel), FaceQuickNamingService.getPeople()]);
			entries.value = adaptFaceQueue(queue.data).map(toEntry);
			isTruncated.value = queue.data.is_truncated;
			people.value = adaptPeopleList(list.data);
			index.value = 0;
			preloadAround();
		} catch {
			loadError.value = true;
		} finally {
			isLoading.value = false;
		}
	}

	/** Fetch more faces when the end of a capped queue is reached; already seen faces are dropped. */
	async function refetch(): Promise<void> {
		isRefetching.value = true;
		try {
			const queue = await FaceQuickNamingService.getQueue(clusterLabel);
			const seen = new Set(entries.value.map((e) => e.face.id));
			const fresh = adaptFaceQueue(queue.data).filter((f) => !seen.has(f.id));
			entries.value.push(...fresh.map(toEntry));
			isTruncated.value = queue.data.is_truncated && fresh.length > 0;
			preloadAround();
		} catch {
			loadError.value = true;
		} finally {
			isRefetching.value = false;
		}
	}

	/** Reload the people list, keeping persons still being created (FR-093-11). */
	async function reloadPeople(): Promise<void> {
		try {
			const list = await FaceQuickNamingService.getPeople();
			const pending = people.value.filter((p) => p.isPending);
			people.value = [...adaptPeopleList(list.data), ...pending];
		} catch {
			// Keep the current list; the next action reports any real failure.
		}
	}

	function goTo(target: number): void {
		index.value = Math.max(0, Math.min(target, entries.value.length));
		preloadAround();
		if (index.value >= entries.value.length && isTruncated.value && !isRefetching.value) {
			void refetch();
		}
	}

	function advance(): void {
		goTo(index.value + 1);
	}

	function back(): void {
		if (index.value > 0) {
			goTo(index.value - 1);
		}
	}

	function skip(): void {
		const entry = current.value;
		if (entry !== null && entry.status === "open") {
			entry.status = "skipped";
		}
		advance();
	}

	/** Run `task` after the previous requests of the same face; mark the entry failed on error. */
	function enqueue(entry: SessionEntry, keptText: string, task: () => Promise<void>): Promise<void> {
		const previous = faceChains.get(entry.face.id) ?? Promise.resolve();
		const next = previous
			.then(task)
			.then(() => {
				entry.failed = false;
				entry.failedText = "";
			})
			.catch(() => {
				entry.failed = true;
				entry.failedText = keptText;
				entry.status = "open";
				entry.person = null;
				onFailure(entries.value.indexOf(entry));
			});
		faceChains.set(entry.face.id, next);
		return next;
	}

	async function undismissIfNeeded(entry: SessionEntry): Promise<void> {
		if (entry.mayBeDismissed) {
			await FaceQuickNamingService.setDismissed(entry.face.id, false);
			entry.mayBeDismissed = false;
		}
	}

	function resolvePersonId(person: PersonOption): Promise<string> {
		if (!person.isPending) {
			return Promise.resolve(person.id);
		}
		return pendingCreations.get(person.id) ?? Promise.reject(new Error("unknown pending person"));
	}

	function bumpFaceCount(personId: string): void {
		const person = people.value.find((p) => p.id === personId);
		if (person !== undefined) {
			person.faceCount++;
		}
	}

	/** Assign the current face to an existing (or pending) person. */
	function assignExisting(person: PersonOption): void {
		const entry = current.value;
		if (entry === null) {
			return;
		}
		entry.status = "named";
		entry.person = { id: person.id, name: person.name };
		entry.failed = false;
		advance();

		void enqueue(entry, person.name, async () => {
			await undismissIfNeeded(entry);
			const id = await resolvePersonId(person);
			await FaceQuickNamingService.assignPerson(entry.face.id, id);
			if (entry.person !== null) {
				entry.person.id = id;
			}
			bumpFaceCount(id);
		});
	}

	/** Assign the current face to the suggested person. */
	function acceptSuggestion(): void {
		const s = suggestion.value;
		if (s === null) {
			return;
		}
		const person = people.value.find((p) => p.id === s.personId) ?? {
			id: s.personId,
			name: s.name,
			faceCount: 0,
			cropUrl: null,
			isPending: false,
		};
		assignExisting(person);
	}

	/** Create a new person named `name` from the current face (create entry or Shift+Enter). */
	function createPerson(name: string): void {
		const entry = current.value;
		const trimmed = name.trim();
		if (entry === null || trimmed === "") {
			return;
		}
		pendingCounter++;
		const option: PersonOption = { id: `pending:${pendingCounter}`, name: trimmed, faceCount: 1, cropUrl: entry.face.cropUrl, isPending: true };
		const pendingId = option.id;
		people.value.push(option);

		entry.status = "named";
		entry.person = { id: pendingId, name: trimmed };
		entry.failed = false;
		advance();

		let resolveId: (id: string) => void = () => {};
		let rejectId: (reason: unknown) => void = () => {};
		const idPromise = new Promise<string>((resolve, reject) => {
			resolveId = resolve;
			rejectId = reject;
		});
		// Failures are reported on the faces waiting for this person.
		idPromise.catch(() => {});
		pendingCreations.set(pendingId, idPromise);

		void enqueue(entry, trimmed, async () => {
			try {
				await undismissIfNeeded(entry);
				const response = await FaceQuickNamingService.assignNewPerson(entry.face.id, trimmed);
				const id = response.data.person_id;
				if (id === null) {
					throw new Error("person not created");
				}
				option.id = id;
				option.isPending = false;
				if (entry.person !== null) {
					entry.person.id = id;
				}
				resolveId(id);
			} catch (error) {
				people.value = people.value.filter((p) => p !== option);
				rejectId(error);
				throw error;
			} finally {
				pendingCreations.delete(pendingId);
			}
		});
	}

	/** Un-assign a revisited named face (clear + Enter, FR-093-05). */
	function unassign(): void {
		const entry = current.value;
		if (entry === null || entry.status !== "named") {
			return;
		}
		entry.status = "skipped";
		entry.person = null;
		advance();

		void enqueue(entry, "", async () => {
			await FaceQuickNamingService.unassign(entry.face.id);
		});
	}

	/** Discard the current face (FR-093-06); reload the people list when it was named (FR-093-11). */
	function discard(): void {
		const entry = current.value;
		if (entry === null) {
			return;
		}
		if (entry.status === "discarded") {
			advance();
			return;
		}
		const wasNamed = entry.status === "named";
		entry.status = "discarded";
		entry.person = null;
		entry.mayBeDismissed = true;
		entry.failed = false;
		advance();

		void enqueue(entry, "", async () => {
			await FaceQuickNamingService.setDismissed(entry.face.id, true);
			if (wasNamed) {
				await reloadPeople();
			}
		});
	}

	function preloadAround(): void {
		const start = index.value;
		entries.value.slice(start, start + PRELOAD_AHEAD + 1).forEach((entry) => loadPhoto(entry.face.id));
		entries.value.slice(0, Math.max(0, start - KEEP_BEHIND)).forEach((entry) => releasePhoto(entry.face.id));
	}

	function loadPhoto(faceId: string): void {
		if (photoUrls.has(faceId) || photoRequests.has(faceId)) {
			return;
		}
		const controller = new AbortController();
		photoRequests.set(faceId, controller);
		FaceQuickNamingService.getPhoto(faceId, "medium", controller.signal)
			.then((url) => photoUrls.set(faceId, url))
			.catch(() => {})
			.finally(() => photoRequests.delete(faceId));
	}

	function releasePhoto(faceId: string): void {
		photoRequests.get(faceId)?.abort();
		photoRequests.delete(faceId);
		const url = photoUrls.get(faceId);
		if (url !== undefined) {
			URL.revokeObjectURL(url);
			photoUrls.delete(faceId);
		}
	}

	function dispose(): void {
		[...photoRequests.keys(), ...photoUrls.keys()].forEach(releasePhoto);
	}

	return {
		entries,
		people,
		index,
		current,
		suggestion,
		isTruncated,
		isLoading,
		isRefetching,
		loadError,
		isDone,
		photoUrls,
		load,
		goTo,
		back,
		skip,
		assignExisting,
		acceptSuggestion,
		createPerson,
		unassign,
		discard,
		dispose,
	};
}
