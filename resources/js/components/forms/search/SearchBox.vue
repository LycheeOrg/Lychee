<template>
	<div
		class="flex items-center justify-center w-full flex-wrap mt-5 mb-5 bg-none transition-all"
		:class="search.length < props.searchMinimumLengh ? 'h-4/5' : ''"
	>
		<div class="w-full flex items-center flex-wrap justify-center">
			<div class="items-center relative text-right">
				<InputText
					id="searchQuery"
					type="text"
					v-model="search"
					:invalid="!isValid"
					:placeholder="$t('lychee.SEARCH')"
					@updated="debouncedFn"
				/>
			</div>
			<div class="items-center text-danger-700 w-full text-center" :class="!isValid ? 'opacity-100' : 'opacity-0'">
				Minimum {{ props.searchMinimumLengh }} characters required.
			</div>
		</div>
	</div>
</template>
<script lang="ts" setup>
import InputText from "@/components/forms/basic/InputText.vue";
import { computed } from "@vue/reactivity";
import { useDebounceFn } from "@vueuse/core";

const props = defineProps<{
	searchMinimumLengh: number;
}>();

const search = defineModel<string>("search", { required: true });

const emits = defineEmits<{
	search: [terms: string];
	clear: [];
}>();

const isValid = computed<boolean>(() => {
	return search.value === "" || search.value.length >= props.searchMinimumLengh;
});

const debouncedFn = useDebounceFn(() => {
	if (search.value === "" || !isValid.value) {
		emits("clear");
		return;
	}

	if (search.value !== undefined && search.value.length >= props.searchMinimumLengh) {
		emits("search", search.value);
	}
}, 1000);
</script>
