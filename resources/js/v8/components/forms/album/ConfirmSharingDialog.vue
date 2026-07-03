<template>
	<UModal v-model:open="open">
		<template #body>
			<p class="mb-4 text-center text-highlighted text-wrap" v-html="$t('sharing.propagate_help')"></p>
			<p class="mb-8 text-center text-wrap" v-html="$t('sharing.propagate_default')"></p>
			<div class="flex items-start w-full">
				<UCheckbox v-model="shallOverride" input-id="shallOverride" />
				<label for="shallOverride" class="ltr:ml-2 rtl:mr-2">
					<span v-html="$t('sharing.propagate_overwrite')"></span><br />
					<span class="text-warning">
						<UIcon name="prime:exclamation-triangle" class="ltr:mr-2 rtl:ml-2" />{{ $t("sharing.propagate_warning") }}
					</span>
				</label>
			</div>
		</template>
		<template #footer>
			<UButton color="neutral" variant="ghost" class="w-full font-bold justify-center" @click="open = false">
				{{ $t("dialogs.button.cancel") }}
			</UButton>
			<UButton icon="prime:forward" color="error" class="w-full justify-center font-bold" :label="$t('sharing.propagate')" @click="propagate" />
		</template>
	</UModal>
</template>
<script setup lang="ts">
import SharingService from "@/services/sharing-service";
import { trans } from "laravel-vue-i18n";
import { useAppToast } from "@/v8/composables/useAppToast";
import { ref } from "vue";

const props = defineProps<{
	album:
		| App.Http.Resources.Models.HeadAlbumResource
		| App.Http.Resources.Models.HeadTagAlbumResource
		| App.Http.Resources.Models.HeadPersonAlbumResource;
}>();

const open = defineModel<boolean>("open", { required: true });
const toast = useAppToast();
const shallOverride = ref(false);

function reset() {
	shallOverride.value = false;
}

function propagate() {
	const data = {
		album_id: props.album.id,
		shall_override: shallOverride.value,
	};

	SharingService.propagate(data)
		.then(() => {
			if (shallOverride.value) {
				toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("sharing.permission_overwritten"), life: 3000 });
			} else {
				toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("sharing.permission_updated"), life: 3000 });
			}
			open.value = false;
			reset();
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: error.response.data.message, life: 3000 });
		});
}
</script>
