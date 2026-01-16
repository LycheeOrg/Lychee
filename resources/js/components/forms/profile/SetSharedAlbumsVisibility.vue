<template>
	<Fieldset
		v-if="user && user.id !== null"
		:legend="$t('profile.preferences.header')"
		:toggleable="true"
		class="mb-4 hover:border-primary-500 pt-2 max-w-xl mx-auto"
	>
		<form>
			<div class="w-full mb-6">
				<div class="pb-4 text-muted-color">
					{{ $t("profile.shared_albums.instruction") }}
				</div>
				<Select
					v-model="selectedMode"
					:options="visibilityOptions"
					option-label="label"
					option-value="value"
					class="w-full border-none"
					@change="markChanged"
				>
					<template #option="slotProps">
						<div class="flex items-center justify-between w-full gap-3">
							<span class="text-muted-color-emphasis w-full">{{ slotProps.option.label }}</span>
							<span class="text-xs text-muted-color w-full ltr:text-right rtl:text-left">{{ slotProps.option.description }}</span>
						</div>
					</template>
				</Select>
			</div>
			<div class="flex w-full mt-4">
				<Button severity="contrast" class="w-full font-bold border-none shrink rounded-none ltr:rounded-l-xl rtl:rounded-r-xl" @click="save">
					{{ $t("profile.preferences.save") }}
				</Button>
				<Button
					severity="secondary"
					class="w-full font-bold border-none shrink rounded-none ltr:rounded-r-xl rtl:rounded-l-xl"
					@click="reset"
				>
					{{ $t("profile.preferences.reset") }}
				</Button>
			</div>
		</form>
	</Fieldset>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import Button from "primevue/button";
import Select from "primevue/select";
import { useToast } from "primevue/usetoast";
import ProfileService from "@/services/profile-service";
import { trans } from "laravel-vue-i18n";
import Fieldset from "@/components/forms/basic/Fieldset.vue";
import { useUserStore } from "@/stores/UserState";
import { storeToRefs } from "pinia";

const userStore = useUserStore();
const { user } = storeToRefs(userStore);

const selectedMode = ref<string>("default");

const visibilityOptions = computed(() => [
	{
		value: "default",
		label: trans("profile.shared_albums.mode_default"),
		description: trans("profile.shared_albums.mode_default_desc"),
	},
	{
		value: "show",
		label: trans("profile.shared_albums.mode_show"),
		description: trans("profile.shared_albums.mode_show_desc"),
	},
	{
		value: "separate",
		label: trans("profile.shared_albums.mode_separate"),
		description: trans("profile.shared_albums.mode_separate_desc"),
	},
	{
		value: "separate_shared_only",
		label: trans("profile.shared_albums.mode_separate_shared_only"),
		description: trans("profile.shared_albums.mode_separate_shared_only_desc"),
	},
	{
		value: "hide",
		label: trans("profile.shared_albums.mode_hide"),
		description: trans("profile.shared_albums.mode_hide_desc"),
	},
]);
const hasChanged = ref(false);
const toast = useToast();

function markChanged(): void {
	hasChanged.value = true;
}

function reset(): void {
	selectedMode.value = user.value?.shared_albums_visibility ?? "default";
	hasChanged.value = false;
}

function save(): void {
	if (!user.value) {
		return;
	}

	ProfileService.updateSharedAlbumsVisibility(selectedMode.value as App.Enum.UserSharedAlbumsVisibility)
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("profile.preferences.change_saved"),
				life: 3000,
			});
			hasChanged.value = false;
			userStore.refresh();
		})
		.catch((error) => {
			toast.add({
				severity: "error",
				summary: trans("toasts.error"),
				detail: error.response?.data?.message || trans("toasts.error"),
				life: 3000,
			});
		});
}

onMounted(() => {
	if (user.value) {
		selectedMode.value = user.value.shared_albums_visibility ?? "default";
	}
});
</script>
