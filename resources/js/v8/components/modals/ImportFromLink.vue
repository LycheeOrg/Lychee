<template>
	<UModal v-model:open="open" :dismissible="true">
		<template #body>
			<p class="mb-5 text-muted text-base">{{ $t("dialogs.import_from_link.instructions") }}</p>
			<form>
				<div class="my-3 first:mt-0 last:mb-0" dir="ltr">
					<UTextarea
						id="links"
						v-model="urls"
						class="w-full"
						:rows="5"
						placeholder="https://&#10;https://&#10;..."
						:color="!isValidInput && urls.length > 0 ? 'error' : undefined"
					/>
				</div>
			</form>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="closeCallback">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" :disabled="!isValidInput || urls.length === 0" @click="submit">
					{{ $t("dialogs.import_from_link.import") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoService from "@/services/photo-service";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import AlbumService from "@/services/album-service";

const open = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{ refresh: [] }>();

const router = useRouter();
const { getParentId } = usePhotoRoute(router);

const urls = ref<string>("");

function submit() {
	PhotoService.importFromUrl(urls.value.split("\n"), getParentId() ?? null).then(() => {
		urls.value = "";
		open.value = false;
		// Clear cache for the parent album to ensure the new photos are displayed
		AlbumService.clearCache(getParentId() ?? "unsorted");
		emits("refresh");
	});
}

const isValidInput = computed(() => urls.value.split("\n").every((url) => url.match(/^https?:\/\/.+/)));

function closeCallback() {
	open.value = false;
}
</script>
