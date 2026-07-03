<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container>
			<div class="p-9 text-center text-muted-color">
				<p class="text-sm/8 font-bold mb-2">{{ $t("dialogs.watermark_confirm.title") }}</p>
				<p class="text-sm/8">{{ $t("dialogs.watermark_confirm.description") }}</p>
			</div>
			<div class="flex">
				<Button severity="secondary" class="font-bold w-full border-none rounded-none rounded-bl-xl" @click="close">
					{{ $t("dialogs.button.cancel") }}
				</Button>
				<Button severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" @click="confirm">
					{{ $t("dialogs.watermark_confirm.confirm") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	albumId: string | undefined;
}>();

const visible = defineModel<boolean>("visible", { default: false });

const emits = defineEmits<{
	watermarked: [];
}>();

const toast = useToast();

function close() {
	visible.value = false;
}

function confirm() {
	if (props.albumId === undefined) {
		return;
	}

	AlbumService.watermark(props.albumId).then(() => {
		toast.add({
			severity: "success",
			detail: trans("toasts.success"),
			life: 3000,
		});
		close();
		emits("watermarked");
	});
}
</script>
