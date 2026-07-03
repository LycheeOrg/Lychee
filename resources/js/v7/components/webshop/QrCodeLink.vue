<template>
	<canvas ref="canvasRef"></canvas>
</template>
<script setup lang="ts">
import { watch, ref, nextTick } from "vue";
import QRCode from "qrcode";

const props = defineProps<{ url: string }>();
const canvasRef = ref<HTMLCanvasElement | null>(null);

watch(
	() => props.url,

	async (newUrl: string) => {
		await nextTick();
		if (!canvasRef.value || !newUrl) {
			return;
		}

		QRCode.toCanvas(
			canvasRef.value,
			newUrl,
			{
				errorCorrectionLevel: "H",
				// fill: '#000000',
				// background: '#FFFFFF',
				// size: 300,
			},
			function (err: Error | null | undefined) {
				if (err) {
					console.error("QR code generation failed:", err);
				}
			},
		);
	},
	{ immediate: true },
);
</script>
