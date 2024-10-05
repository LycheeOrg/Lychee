<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative max-w-xl text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("lychee.ALBUM_PASSWORD_REQUIRED") }}</p>
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel>
						<InputText id="albumPassword" v-model="password" @keydown.enter="unlock" />
						<label class="" for="albumPassword">{{ $t("lychee.PASSWORD") }}</label>
					</FloatLabel>
				</div>
				<div class="flex items-center mt-9">
					<Button @click="hide" severity="secondary" class="w-full font-bold border-none rounded-bl-xl">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button
						severity="contrast"
						class="font-bold w-full border-none rounded-none rounded-br-xl"
						:disabled="!deactivate"
						@click="unlock"
					>
						{{ $t("lychee.ENTER") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import { computed, ref } from "vue";
import InputText from "../basic/InputText.vue";

const props = defineProps<{
	albumid: string;
}>();
const visible = defineModel("visible", { default: false });

const emits = defineEmits<{
	reload: [];
	fail: [];
}>();

const password = ref<string | undefined>(undefined);
const deactivate = computed(() => password.value !== undefined && password.value.length > 0);

function unlock() {
	if (password.value === undefined) {
		return;
	}

	AlbumService.unlock(props.albumid, password.value)
		.then((_response) => {
			AlbumService.clearAlbums();
			AlbumService.clearCache(props.albumid);
			emits("reload");
		})
		.catch((_error) => {
			visible.value = false;
			emits("fail");
		});
}

function hide() {
	visible.value = false;
	history.back();
}
</script>
