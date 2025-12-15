<template>
	<canvas id="canvas"></canvas>
</template>
<script setup lang="ts">
import { watch } from "vue";
import QRCode from "qrcode";

const props = defineProps<{ url: string }>();

watch(
	() => props.url,
	(newUrl: string) => {
		QRCode.toCanvas(
			document.getElementById("canvas"),
			newUrl,
			{
				errorCorrectionLevel: "H",
				// fill: '#000000',
				// background: '#FFFFFF',
				// size: 300,
			},
			function (err: Error | null | undefined) {
				if (err) throw err;
			},
		);
	},
	{ immediate: true },
);
</script>
