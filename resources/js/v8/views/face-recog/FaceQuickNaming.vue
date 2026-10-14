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
			<!-- Photo with the current face highlighted (FR-093-01) -->
			<div class="flex-1 min-h-0 w-full flex items-center justify-center">
				<div v-if="photoUrl !== undefined" class="relative inline-block max-h-full max-w-full overflow-hidden rounded-lg">
					<img :src="photoUrl" alt="" class="block max-h-[calc(100svh-22rem)] max-w-full object-contain select-none" draggable="false" />
					<div class="absolute rounded-sm ring-2 ring-primary pointer-events-none quick-naming-highlight" :style="highlightStyle" />
				</div>
				<div v-else class="flex flex-col items-center gap-3">
					<img v-if="currentEntry.face.cropUrl" :src="currentEntry.face.cropUrl" alt="" class="w-40 h-40 rounded-lg object-cover" />
					<LycheeLoadingIcon fast class="text-2xl" />
				</div>
			</div>

			<!-- Crop, state badge and name box -->
			<div class="w-full max-w-xl flex flex-col gap-2">
				<div class="flex items-center gap-3 min-h-8">
					<img v-if="currentEntry.face.cropUrl" :src="currentEntry.face.cropUrl" alt="" class="w-10 h-10 rounded-full object-cover" />
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
					:is-rtl="isRTL()"
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

const highlightStyle = computed<CSSProperties>(() => {
	const face = currentEntry.value.face;
	return {
		left: `${face.x * 100}%`,
		top: `${face.y * 100}%`,
		width: `${face.width * 100}%`,
		height: `${face.height * 100}%`,
	};
});

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

onMounted(() => window.addEventListener("focus", focusInput));
onBeforeUnmount(() => {
	window.removeEventListener("focus", focusInput);
	session.value?.dispose();
});
</script>

<style scoped>
/* Dim everything outside the highlighted face (FR-093-01). */
.quick-naming-highlight {
	box-shadow: 0 0 0 9999px rgb(0 0 0 / 0.55);
}
</style>
