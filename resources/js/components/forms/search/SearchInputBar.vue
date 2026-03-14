<template>
	<div class="flex items-center w-full gap-2 flex-wrap mt-5 mb-2 px-4">
		<div class="flex items-center flex-1 min-w-0 gap-2">
			<InputText
				id="searchQuery"
				v-model="modelValue"
				type="text"
				class="flex-1 min-w-0"
				:invalid="!isValid && modelValue !== ''"
				:placeholder="$t('gallery.search.searchbox')"
				@keyup.enter="onEnterKey"
			/>
			<Button
				:disabled="!isValid || modelValue === ''"
				:label="$t('gallery.search.advanced.search_button')"
				severity="primary"
				class="shrink-0"
				@click="emits('search')"
			/>
			<Button
				:aria-label="$t('gallery.search.advanced.toggle_advanced')"
				severity="secondary"
				text
				class="shrink-0"
				:icon="advancedOpen ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
				@click="advancedOpen = !advancedOpen"
			/>
		</div>
		<div
			:class="{
				'w-full text-sm text-danger-700 transition-opacity': true,
				'opacity-100': !isValid && modelValue !== '',
				'opacity-0': isValid || modelValue === '',
			}"
		>
			{{ sprintf($t("gallery.search.minimum_chars"), minLength) }}
		</div>
	</div>
</template>
<script lang="ts" setup>
import { computed } from "vue";
import Button from "primevue/button";
import { sprintf } from "sprintf-js";
import InputText from "@/components/forms/basic/InputText.vue";

const modelValue = defineModel<string>({ default: "" });
const advancedOpen = defineModel<boolean>("advancedOpen", { default: false });

const props = defineProps<{
	minLength: number;
}>();

const emits = defineEmits<{
	search: [];
}>();

const isValid = computed<boolean>(() => modelValue.value.length >= props.minLength);

function onEnterKey() {
	if (isValid.value && modelValue.value !== "") {
		emits("search");
	}
}
</script>
