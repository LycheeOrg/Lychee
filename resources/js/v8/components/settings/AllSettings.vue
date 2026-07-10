<template>
	<div v-if="configs">
		<div class="flex relative items-start flex-row-reverse justify-between gap-8">
			<UPageAside class="top-11 hidden sticky sm:block">
				<UNavigationMenu orientation="vertical" :items="navItems" highlight />
			</UPageAside>
			<div id="allSettings" class="w-full">
				<Fieldset
					v-for="(configGroup, key) in props.configs"
					:id="String(key)"
					:key="key"
					:legend="tCatName({ key: configGroup.cat, name: configGroup.name })"
					:toggleable="true"
					class="mb-4 hover:border-primary-500 pt-2"
				>
					<ConfigGroup :configs="configGroup.configs" @filled="filled" @reset="reset" />
				</Fieldset>
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import ConfigGroup from "./ConfigGroup.vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import { useTranslation } from "@/composables/useTranslation";
import type { NavigationMenuItem } from "@nuxt/ui";

const { tCatName } = useTranslation();

const props = defineProps<{
	configs: App.Http.Resources.Models.ConfigCategoryResource[];
	hash: string;
}>();

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

function reset(configKey: string) {
	emits("reset", configKey);
}

function filled(key: string, value: string) {
	emits("filled", key, value);
}

// Index of the settings category currently scrolled into view, tracked via IntersectionObserver
// below (replaces the previous @sidsbrmnn/scrollspy dependency).
const activeIndex = ref(0);

const navItems = computed<NavigationMenuItem[]>(() => {
	if (!props.configs) {
		return [];
	}
	return props.configs.map((c, key) => ({
		label: tCatName({ key: c.cat, name: c.name }),
		active: activeIndex.value === key,
		onSelect: (e: Event) => {
			e.preventDefault();
			goto(key);
		},
	}));
});

function goto(index: number) {
	const el = document.getElementById(String(index));
	if (el) {
		el.scrollIntoView({ behavior: "smooth" });
	}
}

let observer: IntersectionObserver | null = null;

function cleanupObserver() {
	observer?.disconnect();
	observer = null;
}

function setupObserver() {
	cleanupObserver();

	const sections = props.configs.map((_c, key) => document.getElementById(String(key))).filter((el): el is HTMLElement => el !== null);

	if (sections.length === 0) {
		return;
	}

	// Triggers once a section crosses roughly the top third of the viewport, so the highlighted
	// nav item tracks whichever section is currently "in reading position", not just any overlap.
	observer = new IntersectionObserver(
		(entries) => {
			for (const entry of entries) {
				if (entry.isIntersecting) {
					const index = sections.indexOf(entry.target as HTMLElement);
					if (index !== -1) {
						activeIndex.value = index;
					}
				}
			}
		},
		{ rootMargin: "-10% 0px -70% 0px", threshold: 0 },
	);

	sections.forEach((el) => observer?.observe(el));
}

onMounted(() => {
	nextTick(() => setupObserver());
});

onUnmounted(cleanupObserver);

watch(
	() => props.hash,
	() => {
		activeIndex.value = 0;
		nextTick(() => setupObserver());
	},
);
</script>
