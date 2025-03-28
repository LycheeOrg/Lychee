<template>
	<div v-if="configs">
		<div class="flex relative items-start flex-row-reverse justify-between gap-8">
			<Menu :model="sections" class="top-11 border-none hidden sticky sm:block" id="navMain">
				<template #item="{ item, props }">
					<a
						:href="item.link"
						class="nav-link block hover:text-primary-400 border-l border-solid border-surface-700 hover:border-primary-400 px-4 capitalize"
						@click.prevent="goto(item.link)"
					>
						<span>{{ item.label }}</span>
					</a>
				</template>
			</Menu>
			<div class="w-full" id="allSettings">
				<Fieldset
					v-for="(configGroup, key) in props.configs"
					:legend="configGroup.name"
					:toggleable="true"
					class="border-b-0 border-r-0 rounded-r-none rounded-b-none mb-4 hover:border-primary-500 pt-2"
					:pt:legendlabel:class="'capitalize'"
					:id="key"
				>
					<ConfigGroup :configs="configGroup.configs" @filled="filled" @reset="reset" />
				</Fieldset>
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed, onUpdated, ref } from "vue";
import Menu from "primevue/menu";
import Fieldset from "primevue/fieldset";
// @ts-expect-error
import scrollSpy from "@sidsbrmnn/scrollspy";
import ConfigGroup from "./ConfigGroup.vue";
import { onMounted } from "vue";

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
			label: c.name,
			link: "#" + key,
		};
	});
});

function load(configs: App.Http.Resources.Models.ConfigCategoryResource[]) {
	active.value = configs.map((c, i: number) => i.toString());
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
		sectionSelector: "#allSettings .p-fieldset", // Query selector to your sections
		targetSelector: ".nav-link", // Query select
		activeClass: "!text-primary-500 !border-primary-500",
	});
	// Set the first section as active.
	const admin = spy.sections[0];
	const adminMenuItem = spy.getCurrentMenuItem(admin);
	spy.setActive(adminMenuItem, admin);
});
</script>
