<template>
	<Fieldset
		v-if="user && user.id !== null"
		:legend="$t('profile.preferences.header')"
		:toggleable="true"
		class="mb-4 hover:border-primary pt-2 max-w-xl mx-auto"
	>
		<form>
			<div class="w-full mb-6">
				<div class="pb-4 text-muted">
					{{ $t("profile.shared_albums.instruction") }}
				</div>
				<USelectMenu v-model="selectedMode" :items="visibilityOptions" value-key="value" label-key="label" class="w-full" @update:model-value="markChanged">
					<template #item-label="{ item }">
						<div class="flex items-center justify-between w-full gap-3">
							<span class="text-highlighted w-full">{{ item.label }}</span>
							<span class="text-xs text-muted w-full ltr:text-right rtl:text-left">{{ item.description }}</span>
						</div>
					</template>
				</USelectMenu>
			</div>
			<div class="flex w-full mt-4">
				<UButton color="neutral" class="w-full font-bold shrink rounded-none ltr:rounded-l-xl rtl:rounded-r-xl justify-center" @click="save">
					{{ $t("profile.preferences.save") }}
				</UButton>
				<UButton
					color="neutral"
					variant="soft"
					class="w-full font-bold shrink rounded-none ltr:rounded-r-xl rtl:rounded-l-xl justify-center"
					@click="reset"
				>
					{{ $t("profile.preferences.reset") }}
				</UButton>
			</div>
		</form>
	</Fieldset>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import ProfileService from "@/services/profile-service";
import { trans } from "laravel-vue-i18n";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
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
const toast = useAppToast();

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
