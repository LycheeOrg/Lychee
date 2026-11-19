<template>
	<div class="h-svh flex flex-col overflow-hidden">
		<UHeader :toggle="false">
			<template #left>
				<GoBack @go-back="leave" />
			</template>
			{{ $t("people.quick_naming.title") }}
			<template #right>
				<span v-if="session !== null && session.entries.value.length > 0" class="text-sm text-muted tabular-nums">
					{{ progressLabel }}
				</span>
				<UTooltip :text="$t('people.quick_naming.help')">
					<UButton
						icon="lucide:circle-help"
						color="neutral"
						variant="ghost"
						:aria-label="$t('people.quick_naming.help')"
						@click="isHelpOpen = true"
					/>
				</UTooltip>
			</template>
		</UHeader>

		<FaceRecognitionWarning />

		<div v-if="isForbidden" class="text-muted text-center mt-20 p-4">{{ $t("people.quick_naming.forbidden") }}</div>

		<div
			v-else-if="session === null || session.isLoading.value || (session.current.value === null && session.isRefetching.value)"
			class="flex justify-center items-center mt-20"
		>
			<LycheeLoadingIcon fast class="text-4xl" />
		</div>

		<div v-else-if="session.loadError.value" class="flex flex-col items-center gap-4 mt-20 p-4">
			<span class="text-muted">{{ $t("people.quick_naming.load_error") }}</span>
			<UButton :label="$t('people.quick_naming.retry')" color="neutral" variant="outline" icon="lucide:refresh-cw" @click="session.load()" />
		</div>

		<div v-else-if="session.isDone.value || session.current.value === null" class="flex flex-col items-center gap-4 mt-20 p-4">
			<UIcon name="lucide:circle-check" class="text-5xl text-success" />
			<span class="text-lg">{{ $t("people.quick_naming.done") }}</span>
			<div class="flex gap-2">
				<UButton
					v-if="session.index.value > 0"
					:label="$t('people.quick_naming.previous')"
					color="neutral"
					variant="ghost"
					@click="session.back()"
				/>
				<UButton :label="backLabel" color="neutral" variant="outline" @click="leave" />
			</div>
		</div>

		<div v-else class="flex-1 min-h-0 flex flex-col items-center gap-4 p-4">
			<!-- Photo and name panel: side by side from lg, the panel on the inline-end side (FR-093-16) -->
			<div class="flex-1 min-h-0 w-full flex flex-col lg:flex-row items-center gap-4">
				<!-- Photo with the current face highlighted (FR-093-01) -->
				<div ref="photoBox" class="flex-1 min-h-0 min-w-0 w-full self-stretch relative">
					<template v-if="photoUrl !== undefined">
						<img
							:src="photoUrl"
							alt=""
							class="absolute inset-0 w-full h-full object-contain select-none"
							draggable="false"
							@load="onPhotoLoad"
						/>
						<!-- Overlay clipped to the rendered image rectangle, so the highlight always sits on the face. -->
						<div v-if="imageRect !== null" class="absolute overflow-hidden pointer-events-none" :style="imageRect">
							<div class="absolute rounded-sm ring-2 ring-primary quick-naming-highlight" :style="highlightStyle" />
						</div>
					</template>
					<div v-else class="absolute inset-0 flex flex-col items-center justify-center gap-3">
						<img v-if="currentEntry.face.cropUrl" :src="currentEntry.face.cropUrl" alt="" class="w-40 h-40 rounded-lg object-cover" />
						<LycheeLoadingIcon fast class="text-2xl" />
					</div>
				</div>

				<!-- Crop, sharpness, state badge and name box -->
				<div class="w-full max-w-xl lg:w-96 lg:shrink-0 flex flex-col gap-2">
					<div class="flex flex-wrap items-center gap-x-3 gap-y-1 min-h-8">
						<img v-if="currentEntry.face.cropUrl" :src="currentEntry.face.cropUrl" alt="" class="w-10 h-10 rounded-full object-cover" />
						<!-- Sharpness, red below the face maintenance threshold (FR-093-15) -->
						<span class="text-xs tabular-nums" :class="isBlurry ? 'text-error' : 'text-muted'">
							{{ $t("people.laplacian_variance") }}: {{ Math.round(currentEntry.face.laplacianVariance) }}
						</span>
						<UBadge
							v-if="currentEntry.failed"
							:label="$t('people.quick_naming.badge_failed')"
							color="error"
							variant="subtle"
							icon="lucide:circle-alert"
						/>
						<UBadge
							v-else-if="currentEntry.status === 'named'"
							:label="$t('people.quick_naming.badge_named')"
							color="success"
							variant="subtle"
							icon="lucide:check"
						/>
						<UBadge
							v-else-if="currentEntry.status === 'discarded'"
							:label="$t('people.quick_naming.badge_discarded')"
							color="warning"
							variant="subtle"
							icon="lucide:eye-off"
						/>
						<span class="text-sm text-muted">{{ stateHint }}</span>
					</div>

					<QuickNamingInput
						:key="inputKey"
						ref="nameInput"
						:people="session.people.value"
						:suggestion="session.suggestion.value"
						:prefill="currentEntry.status === 'named' && currentEntry.person !== null ? currentEntry.person.name : null"
						:initial-text="currentEntry.failedText"
						@assign="(p) => session?.assignExisting(p)"
						@create="(name) => session?.createPerson(name)"
						@accept-suggestion="session.acceptSuggestion()"
						@unassign="session.unassign()"
						@keep="session.goTo(session.index.value + 1)"
						@skip="session.skip()"
						@back="session.back()"
						@discard="session.discard()"
					/>
				</div>
			</div>

			<!-- On-screen controls (touch devices, FR-093-04) -->
			<div class="flex gap-2 justify-center">
				<UButton
					:label="$t('people.quick_naming.previous')"
					:icon="isRTL() ? 'lucide:chevron-right' : 'lucide:chevron-left'"
					color="neutral"
					variant="ghost"
					:disabled="session.index.value === 0"
					@click="session.back()"
				/>
				<UButton
					:label="$t('people.quick_naming.discard')"
					icon="lucide:eye-off"
					color="warning"
					variant="outline"
					@click="session.discard()"
				/>
				<UButton
					:label="$t('people.quick_naming.skip')"
					:trailing-icon="isRTL() ? 'lucide:chevron-left' : 'lucide:chevron-right'"
					color="neutral"
					variant="ghost"
					@click="session.skip()"
				/>
			</div>
			<div class="hidden sm:flex flex-wrap gap-x-4 gap-y-1 justify-center text-xs text-muted">
				<span><UKbd value="enter" /> {{ $t("people.quick_naming.hint_name") }}</span>
				<span><UKbd value="shift" /><UKbd value="enter" /> {{ $t("people.quick_naming.hint_new_person") }}</span>
				<span><UKbd :value="isRTL() ? '←' : '→'" /> {{ $t("people.quick_naming.hint_skip") }}</span>
				<span><UKbd :value="isRTL() ? '→' : '←'" /> {{ $t("people.quick_naming.hint_back") }}</span>
				<span><UKbd value="⌫" /> <UKbd value="space" /> {{ $t("people.quick_naming.hint_discard") }}</span>
				<span><UKbd value="escape" /> {{ $t("people.quick_naming.hint_leave") }}</span>
			</div>
		</div>

		<KeybindingsHelp v-model:open="isHelpOpen" only="quick_naming" />
	</div>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, shallowRef, watch, type CSSProperties } from "vue";
import { useRoute, useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";
import { useToast } from "@nuxt/ui/composables/useToast";
import GoBack from "@/v8/components/headers/GoBack.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import FaceRecognitionWarning from "@/v8/components/faceRecog/FaceRecognitionWarning.vue";
import KeybindingsHelp from "@/v8/components/modals/KeybindingsHelp.vue";
import QuickNamingInput from "@/v8/components/forms/people/QuickNamingInput.vue";
import { useQuickNamingSession, type SessionEntry } from "@/v8/composables/useQuickNamingSession";
import { useGlobalRights } from "@/composables/useGlobalRights";
import { useLtRorRtL } from "@/utils/Helpers";

const route = useRoute();
const router = useRouter();
const rawToast = useToast();
const { rights } = useGlobalRights();
const { isRTL } = useLtRorRtL();

const isHelpOpen = ref(false);
const photoBox = ref<HTMLElement | null>(null);
const naturalSize = ref<{ width: number; height: number } | null>(null);
const boxSize = ref<{ width: number; height: number }>({ width: 0, height: 0 });
let resizeObserver: ResizeObserver | null = null;
const nameInput = ref<InstanceType<typeof QuickNamingInput> | null>(null);

/** `?cluster=<label>` scopes the queue to one cluster (FR-093-07). */
const clusterLabel = computed<number | null>(() => {
	const raw = Number.parseInt(String(route.query.cluster ?? ""), 10);
	return Number.isInteger(raw) && raw >= 0 ? raw : null;
});
/** `?from=clusters` makes "Back" return to the face review page (FR-093-14). */
const fromClusters = computed(() => route.query.from === "clusters");
const backLabel = computed(() => (fromClusters.value ? trans("people.quick_naming.back_to_clusters") : trans("people.quick_naming.back_to_people")));

const isForbidden = computed(() => rights.value !== undefined && rights.value !== null && rights.value.modules.can_quick_name_faces === false);

const session = shallowRef<ReturnType<typeof useQuickNamingSession> | null>(null);

/** Only read while the template has checked that a current face exists. */
const currentEntry = computed<SessionEntry>(() => session.value?.current.value as SessionEntry);

const photoUrl = computed<string | undefined>(() => {
	const entry = session.value?.current.value ?? null;
	return entry === null ? undefined : session.value?.photoUrls.get(entry.face.id);
});

/** Rectangle of the photo as drawn with object-contain inside the photo box. */
const imageRect = computed<CSSProperties | null>(() => {
	const natural = naturalSize.value;
	const box = boxSize.value;
	if (natural === null || natural.width === 0 || natural.height === 0 || box.width === 0 || box.height === 0) {
		return null;
	}
	const scale = Math.min(box.width / natural.width, box.height / natural.height);
	const width = natural.width * scale;
	const height = natural.height * scale;
	return {
		left: `${(box.width - width) / 2}px`,
		top: `${(box.height - height) / 2}px`,
		width: `${width}px`,
		height: `${height}px`,
	};
});

function onPhotoLoad(event: Event): void {
	const img = event.target as HTMLImageElement;
	naturalSize.value = { width: img.naturalWidth, height: img.naturalHeight };
}

watch(photoUrl, () => {
	naturalSize.value = null;
});

watch(photoBox, (el) => {
	resizeObserver?.disconnect();
	if (el === null) {
		return;
	}
	resizeObserver = new ResizeObserver(([entry]) => {
		boxSize.value = { width: entry.contentRect.width, height: entry.contentRect.height };
	});
	resizeObserver.observe(el);
});

const highlightStyle = computed<CSSProperties>(() => {
	const face = currentEntry.value.face;
	return {
		left: `${face.x * 100}%`,
		top: `${face.y * 100}%`,
		width: `${face.width * 100}%`,
		height: `${face.height * 100}%`,
	};
});

/** Same threshold as the face maintenance page. */
const BLURRY_LAPLACIAN_THRESHOLD = 50;
const isBlurry = computed(() => currentEntry.value.face.laplacianVariance < BLURRY_LAPLACIAN_THRESHOLD);

const progressLabel = computed(() => {
	if (session.value === null) {
		return "";
	}
	const s = session.value;
	const params = { current: String(Math.min(s.index.value + 1, s.entries.value.length)), total: String(s.entries.value.length) };
	return s.isTruncated.value ? trans("people.quick_naming.progress_more", params) : trans("people.quick_naming.progress", params);
});

const stateHint = computed(() => {
	const entry = currentEntry.value;
	if (entry.failed) {
		return trans("people.quick_naming.failed_hint");
	}
	if (entry.status === "named") {
		return trans("people.quick_naming.revisit_named");
	}
	if (entry.status === "discarded") {
		return trans("people.quick_naming.revisit_discarded");
	}
	return session.value?.suggestion.value !== null ? trans("people.quick_naming.suggestion_hint") : "";
});

/** Remount the name box whenever the face or its state changes, so it resets and takes focus. */
const inputKey = computed(() => {
	const entry = currentEntry.value;
	return `${entry.face.id}:${session.value?.index.value}:${entry.status}:${entry.failed}`;
});

function focusInput(): void {
	nextTick(() => nameInput.value?.focus());
}

function leave(): void {
	router.push({ name: fromClusters.value ? "face-clusters" : "people" });
}

function onFailure(index: number): void {
	rawToast.add({
		color: "error",
		title: trans("people.quick_naming.failed_toast"),
		duration: 8000,
		actions: [
			{
				label: trans("people.quick_naming.go_back"),
				color: "neutral",
				variant: "outline",
				onClick: () => {
					session.value?.goTo(index);
					focusInput();
				},
			},
		],
	});
	focusInput();
}

function start(): void {
	if (session.value !== null) {
		return;
	}
	session.value = useQuickNamingSession(clusterLabel.value, onFailure);
	void session.value.load();
}

watch(
	() => rights.value?.modules.can_quick_name_faces,
	(allowed) => {
		if (allowed === true) {
			start();
		}
	},
	{ immediate: true },
);

watch(isHelpOpen, (open) => {
	if (!open) {
		focusInput();
	}
});

/**
 * Escape leaves the view in every state (naming, done, error), like Back (FR-093-17).
 * Capture phase: runs before the help modal's own Escape handling, so an open help only closes.
 */
function onEscape(event: KeyboardEvent): void {
	if (event.key !== "Escape" || event.isComposing || isHelpOpen.value) {
		return;
	}
	event.preventDefault();
	leave();
}

onMounted(() => {
	window.addEventListener("focus", focusInput);
	window.addEventListener("keydown", onEscape, true);
});
onBeforeUnmount(() => {
	window.removeEventListener("focus", focusInput);
	window.removeEventListener("keydown", onEscape, true);
	resizeObserver?.disconnect();
	session.value?.dispose();
});
</script>

<style scoped>
/* Dim everything outside the highlighted face (FR-093-01). */
.quick-naming-highlight {
	box-shadow: 0 0 0 9999px rgb(0 0 0 / 0.55);
}
</style>
