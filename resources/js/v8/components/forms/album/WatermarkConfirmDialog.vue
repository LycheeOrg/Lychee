<template>
	<UModal v-model:open="open">
		<template #body>
			<div class="text-center text-muted">
				<p class="text-sm/8 font-bold mb-2">{{ $t("dialogs.watermark_confirm.title") }}</p>
				<p class="text-sm/8">{{ $t("dialogs.watermark_confirm.description") }}</p>
			</div>
		</template>
		<template #footer>
			<UButton color="neutral" variant="ghost" class="font-bold w-full justify-center" @click="close">
				{{ $t("dialogs.button.cancel") }}
			</UButton>
			<UButton color="primary" class="font-bold w-full justify-center" @click="confirm">
				{{ $t("dialogs.watermark_confirm.confirm") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	albumId: string | undefined;
}>();

const open = defineModel<boolean>("open", { default: false });

const emits = defineEmits<{
	watermarked: [];
}>();

const toast = useAppToast();

function close() {
	open.value = false;
}

function confirm() {
	if (props.albumId === undefined) {
		return;
	}

	AlbumService.watermark(props.albumId).then(() => {
		toast.add({
			severity: "success",
			summary: trans("toasts.success"),
			life: 3000,
		});
		close();
		emits("watermarked");
	});
}
</script>
