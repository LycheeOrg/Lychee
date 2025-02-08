<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative max-w-xl text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("dialogs.unlock.password_required") }}</p>
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel variant="on">
						<InputPassword id="albumPassword" v-model="password" @keydown.enter="unlock" />
						<label for="albumPassword">{{ $t("dialogs.unlock.password") }}</label>
					</FloatLabel>
				</div>
				<div class="flex items-center mt-9">
					<Button @click="hide" severity="secondary" class="w-full font-bold border-none rounded-bl-xl">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button
						severity="contrast"
						class="font-bold w-full border-none rounded-none rounded-br-xl"
						:disabled="!deactivate"
						@click="unlock"
					>
						{{ $t("dialogs.unlock.unlock") }}
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
import InputPassword from "../basic/InputPassword.vue";

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
