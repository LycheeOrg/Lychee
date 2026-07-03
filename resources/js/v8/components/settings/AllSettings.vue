<template>
	<div v-if="configs">
		<div class="flex relative items-start flex-row-reverse justify-between gap-8">
			<nav id="navMain" class="top-11 hidden sticky sm:block">
				<a
					v-for="section in sections"
					:key="section.link"
					:href="section.link"
					class="nav-link block hover:text-primary-400 ltr:border-l rtl:border-r border-solid border-neutral-700 hover:border-primary-400 px-4 capitalize"
					@click.prevent="goto(section.link)"
				>
					<span>{{ section.label }}</span>
				</a>
			</nav>
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
import { computed, onUpdated, ref } from "vue";
// @ts-expect-error There is no type definition for this package
import scrollSpy from "@sidsbrmnn/scrollspy";
import ConfigGroup from "./ConfigGroup.vue";
import { onMounted } from "vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import { useTranslation } from "@/composables/useTranslation";

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

const active = ref<string[]>([]);

const sections = computed(function () {
	if (!props.configs) {
		return [];
	}
	return props.configs.map((c, key) => {
		return {
			label: tCatName({ key: c.cat, name: c.name }),
			link: "#" + key,
		};
	});
});

function load(configs: App.Http.Resources.Models.ConfigCategoryResource[]) {
	active.value = configs.map((_c, i: number) => i.toString());
}

function goto(section: string) {
	const el = document.getElementById(section.slice(1));
	if (el) {
		el.scrollIntoView({ behavior: "smooth" });
	}
}

onMounted(() => load(props.configs));

onUpdated(function () {
	const elem = document.getElementById("navMain");
	if (!elem) {
		return;
	}

	const spy = scrollSpy(document.getElementById("navMain"), {
		sectionSelector: "#allSettings .v8-fieldset", // Query selector to your sections
		targetSelector: ".nav-link", // Query select
		activeClass: "!text-primary-500 !border-primary-500",
	});
	// Set the first section as active.
	const admin = spy.sections[0];
	const adminMenuItem = spy.getCurrentMenuItem(admin);
	spy.setActive(adminMenuItem, admin);
});
</script>
