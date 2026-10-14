<template>
	<div class="w-full max-w-xl flex flex-col gap-1">
		<div class="relative">
			<input
				ref="inputEl"
				v-model="text"
				type="text"
				autocomplete="off"
				spellcheck="false"
				maxlength="255"
				class="w-full rounded-lg border border-default bg-default px-4 py-3 text-lg text-highlighted placeholder:text-dimmed focus:outline-none focus:ring-2 focus:ring-primary"
				:placeholder="placeholder"
				:aria-label="$t('people.quick_naming.placeholder')"
				@keydown="onKeydown"
				@input="highlight = 0"
			/>
			<UBadge
				v-if="text === '' && suggestion !== null"
				:label="$t('people.quick_naming.suggested')"
				color="primary"
				variant="subtle"
				class="absolute top-1/2 -translate-y-1/2 ltr:right-3 rtl:left-3 pointer-events-none"
			/>
		</div>

		<ul v-if="matches.length > 0" class="rounded-lg border border-default bg-default overflow-hidden" role="listbox">
			<li
				v-for="(entry, i) in matches"
				:key="entry.kind === 'person' ? entry.person.id : 'create'"
				role="option"
				:aria-selected="i === highlight"
				class="flex items-center gap-3 px-3 py-2 cursor-pointer"
				:class="i === highlight ? 'bg-elevated' : ''"
				@mousedown.prevent="choose(i)"
			>
				<template v-if="entry.kind === 'person'">
					<img v-if="entry.person.cropUrl" :src="entry.person.cropUrl" alt="" class="w-8 h-8 rounded-full object-cover shrink-0" />
					<span v-else class="w-8 h-8 rounded-full bg-elevated flex items-center justify-center shrink-0">
						<UIcon name="lucide:user" class="text-muted" />
					</span>
					<span class="grow truncate">{{ entry.person.name }}</span>
					<span class="text-sm text-muted shrink-0">{{ entry.person.faceCount }} {{ $t("people.faces_label") }}</span>
				</template>
				<template v-else>
					<span class="w-8 h-8 rounded-full bg-elevated flex items-center justify-center shrink-0">
						<UIcon name="lucide:user-plus" class="text-muted" />
					</span>
					<span class="grow truncate">
						{{
							entry.another
								? $t("people.quick_naming.create_another", { name: entry.name })
								: $t("people.quick_naming.create", { name: entry.name })
						}}
					</span>
					<UKbd value="shift" class="shrink-0" /><UKbd value="enter" class="shrink-0" />
				</template>
			</li>
		</ul>
	</div>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { decideKey, rankMatches, type PersonOption, type Suggestion } from "@/v8/utils/quickNaming";
import { useLtRorRtL } from "@/utils/Helpers";

const props = defineProps<{
	people: PersonOption[];
	suggestion: Suggestion | null;
	/** Pre-filled name of a revisited named face (FR-093-05). */
	prefill: string | null;
	/** Text to restore after a failed request (FR-093-09). */
	initialText: string;
}>();

const emits = defineEmits<{
	assign: [person: PersonOption];
	create: [name: string];
	acceptSuggestion: [];
	unassign: [];
	keep: [];
	skip: [];
	back: [];
	discard: [];
}>();

const { isRTL } = useLtRorRtL();
const inputEl = ref<HTMLInputElement | null>(null);
const text = ref(props.prefill ?? props.initialText);
const highlight = ref(0);

const matches = computed(() => (text.value === props.prefill ? [] : rankMatches(props.people, text.value, props.suggestion?.personId ?? null)));

const placeholder = computed(() => props.suggestion?.name ?? trans("people.quick_naming.placeholder"));

function focus(): void {
	inputEl.value?.focus();
}

function choose(i: number): void {
	const entry = matches.value[i];
	if (entry === undefined) {
		return;
	}
	if (entry.kind === "person") {
		emits("assign", entry.person);
	} else {
		emits("create", entry.name);
	}
}

/** Enter (FR-093-03, FR-093-05). */
function submit(): void {
	const trimmed = text.value.trim();
	if (trimmed === "") {
		if (props.prefill !== null) {
			emits("unassign");
		} else if (props.suggestion !== null) {
			emits("acceptSuggestion");
		}
		return;
	}
	if (props.prefill !== null && text.value === props.prefill) {
		emits("keep");
		return;
	}
	choose(highlight.value);
}

function onKeydown(event: KeyboardEvent): void {
	const action = decideKey(event, {
		text: text.value,
		prefill: props.prefill,
		hasEntries: matches.value.length > 0,
		// Read at key time: the document direction can change with the language (NFR-093-02).
		isRtl: isRTL(),
	});
	if (action === "none") {
		return;
	}
	event.preventDefault();

	switch (action) {
		case "submit":
			submit();
			break;
		case "create":
			emits("create", text.value);
			break;
		case "skip":
			emits("skip");
			break;
		case "back":
			emits("back");
			break;
		case "discard":
			emits("discard");
			break;
		case "clear":
			text.value = "";
			break;
		case "highlight-next":
			highlight.value = (highlight.value + 1) % matches.value.length;
			break;
		case "highlight-prev":
			highlight.value = (highlight.value - 1 + matches.value.length) % matches.value.length;
			break;
	}
}

onMounted(() => nextTick(focus));

defineExpose({ focus });
</script>
