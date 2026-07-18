<template>
	<UModal v-if="photoStore.photo" v-model:open="visible" :dismissible="true" :fullscreen="isMobile">
		<template #body>
			<div class="flex flex-col items-center gap-3 text-center py-4">
				<h2 class="text-xl font-bold wrap-break-word">{{ photoStore.photo.title }}</h2>
				<canvas ref="qrCanvas"></canvas>
				<span v-if="site_owner" class="text-muted text-sm">{{ $t("dialogs.photo_share_card.by", { name: site_owner }) }}</span>
				<span v-if="photoStore.photo.preformatted.license" class="text-muted text-xs">{{ photoStore.photo.preformatted.license }}</span>
			</div>
		</template>
		<template #footer>
			<UButton color="neutral" variant="soft" class="w-full justify-center font-bold" @click="closeCallback">
				{{ $t("dialogs.button.close") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { nextTick, ref, watch } from "vue";
import QRCode from "qrcode";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { storeToRefs } from "pinia";
import { usePhotoStore } from "@/stores/PhotoState";
import { useLycheeStateStore } from "@/stores/LycheeState";

const photoStore = usePhotoStore();
const lycheeStore = useLycheeStateStore();
const { site_owner } = storeToRefs(lycheeStore);

const visible = defineModel<boolean>("open", { default: false });

const breakpoints = useBreakpoints(breakpointsTailwind);
const isMobile = breakpoints.smaller("sm");

const qrCanvas = ref<HTMLCanvasElement | null>(null);

function closeCallback() {
	visible.value = false;
}

watch(visible, async (open) => {
	if (!open) {
		return;
	}
	await nextTick();
	if (qrCanvas.value === null) {
		return;
	}
	QRCode.toCanvas(qrCanvas.value, window.location.href, { errorCorrectionLevel: "H" }, function (err: Error | null | undefined) {
		if (err) throw err;
	});
});
</script>
