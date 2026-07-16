<template>
	<div class="w-full p-2">
		<div class="flex items-center w-full gap-2 flex-wrap">
			<div class="flex items-center flex-1 min-w-0 gap-2">
				<UButton
					v-if="albumTitle"
					color="neutral"
					variant="outline"
					:aria-label="$t('gallery.search.clear_scope')"
					@click="emits('clearScope')"
				>
					<UIcon name="lucide:folder" class="shrink-0 size-3" />
					{{ albumTitle }}
					<UIcon name="lucide:x" class="ml-4" />
				</UButton>
				<UInput
					id="searchQuery"
					v-model="modelValue"
					type="text"
					icon="lucide:search"
					class="flex-1 min-w-0"
					:color="!isValid && modelValue !== '' ? 'error' : undefined"
					:placeholder="$t('gallery.search.searchbox')"
					@keyup.enter="onEnterKey"
				>
				</UInput>
				<UButton
					:disabled="!isValid || modelValue === ''"
					:label="$t('gallery.search.advanced.search_button')"
					color="primary"
					class="shrink-0"
					@click="emits('search')"
				/>
			</div>
			<UButton
				:aria-label="$t('gallery.search.advanced.toggle_advanced')"
				color="neutral"
				:variant="advancedOpen ? 'soft' : 'ghost'"
				class="shrink-0"
				:icon="advancedOpen ? 'lucide:chevron-up' : 'lucide:chevron-down'"
				@click="
					() => {
						advancedOpen = !advancedOpen;
					}
				"
			/>
		</div>
		<div
			:class="{
				'w-full text-sm text-error transition-opacity px-1 pt-1': true,
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
import { useAlbumStore } from "@/stores/AlbumState";

const modelValue = defineModel<string>({ default: "" });
const advancedOpen = defineModel<boolean>("advancedOpen", { default: false });
const albumStore = useAlbumStore();

const props = defineProps<{
	minLength: number;
}>();

const emits = defineEmits<{
	search: [];
	clearScope: [];
}>();

const isValid = computed<boolean>(() => modelValue.value.length >= props.minLength);
const albumTitle = computed<string | undefined>(() => albumStore.album?.title);

function onEnterKey() {
	if (isValid.value && modelValue.value !== "") {
		emits("search");
	}
}
</script>
