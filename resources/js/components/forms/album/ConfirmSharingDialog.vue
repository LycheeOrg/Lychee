<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true" @close="visible = false">
		<template #container="{ closeCallback }">
			<div class="flex flex-col relative md:w-125 max-w-125 text-sm rounded-md pt-9 text-muted-color">
				<p class="px-9 mb-4 text-center text-muted-color-emphasis text-wrap" v-html="$t('sharing.propagate_help')"></p>
				<p class="px-9 mb-8 text-center text-wrap" v-html="$t('sharing.propagate_default')"></p>
				<div class="flex items-start w-full ltr:pr-9 ltr:pl-16 rtl:pl-9 rtl:pr-16">
					<Checkbox v-model="shallOverride" input-id="shallOverride" :binary="true" />
					<label for="shallOverride" class="ltr:ml-2 rtl:mr-2">
						<span v-html="$t('sharing.propagate_overwrite')"></span><br />
						<span class="text-warning-700">
							<i class="pi pi-exclamation-triangle ltr:mr-2 rtl:ml-2" />{{ $t("sharing.propagate_warning") }}
						</span>
					</label>
				</div>
				<div class="flex items-center mt-9 w-full">
					<Button severity="secondary" class="w-full font-bold border-none rounded-bl-xl" @click="closeCallback">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button
						icon="pi pi-forward"
						severity="danger"
						class="w-full border-none rounded-none rounded-br-xl font-bold"
						:label="$t('sharing.propagate')"
						@click="propagate"
					>
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import SharingService from "@/services/sharing-service";
import { trans } from "laravel-vue-i18n";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import Checkbox from "primevue/checkbox";
import Button from "primevue/button";

const props = defineProps<{
	album: App.Http.Resources.Models.HeadAlbumResource | App.Http.Resources.Models.HeadTagAlbumResource;
}>();

const visible = defineModel("visible", { type: Boolean, required: true });
const toast = useToast();
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
			visible.value = false;
			reset();
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: error.response.data.message, life: 3000 });
		});
}
</script>
