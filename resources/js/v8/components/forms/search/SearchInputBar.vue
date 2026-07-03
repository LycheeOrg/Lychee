<template>
	<div class="flex items-center w-full gap-2 flex-wrap mt-5 mb-2 px-4">
		<div class="flex items-center flex-1 min-w-0 gap-2">
			<UInput
				id="searchQuery"
				v-model="modelValue"
				type="text"
				class="flex-1 min-w-0"
				:color="!isValid && modelValue !== '' ? 'error' : undefined"
				:placeholder="$t('gallery.search.searchbox')"
				@keyup.enter="onEnterKey"
			/>
			<UButton
				:disabled="!isValid || modelValue === ''"
				:label="$t('gallery.search.advanced.search_button')"
				color="primary"
				class="shrink-0"
				@click="emits('search')"
			/>
			<UButton
				:aria-label="$t('gallery.search.advanced.toggle_advanced')"
				color="neutral"
				variant="ghost"
				class="shrink-0"
				:icon="advancedOpen ? 'prime:chevron-up' : 'prime:chevron-down'"
				@click="advancedOpen = !advancedOpen"
			/>
		</div>
		<div
			:class="{
				'w-full text-sm text-error transition-opacity': true,
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
import { sprintf } from "sprintf-js";

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
