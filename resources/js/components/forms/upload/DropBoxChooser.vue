<template>
	<div v-if="scriptLoaded && dropboxChooserIsSupported">
		<Button icon="pi pi-box" severity="primary" class="border-none w-64" @click="dropboxIconClicked" label="Open dropbox picker" />
	</div>
</template>

<script setup lang="ts">
import Button from "primevue/button";
import { ref } from "vue";

const props = withDefaults(
	defineProps<{
		apiKey: string;
		multiselect?: boolean;
		linkType?: "preview" | "direct" | undefined;
		folderselect?: boolean;
		sizeLimit?: number;
	}>(),
	{
		linkType: "direct",
		multiselect: true,
		folderselect: false,
		sizeLimit: 0,
	},
);

const emits = defineEmits<{
	picked: [attachments: Dropbox.ChooserFile[]];
	cancel: [];
}>();

const scriptLoaded = ref(false);
const dropboxChooserIsSupported = ref(false);

if (window.Dropbox !== undefined) {
	scriptLoaded.value = true;
} else {
	const dropBoxScript = document.createElement("script");
	dropBoxScript.onload = () => {
		scriptLoaded.value = true;

		if (window.Dropbox === undefined) {
			console.warn("VueDropboxPicker: Dropbox script loaded but window.Dropbox is undefined");
			return;
		}
		// @ts-expect-error
		dropboxChooserIsSupported.value = window.Dropbox.isBrowserSupported();

		if (!dropboxChooserIsSupported.value) {
			console.warn("VueDropboxPicker: This browser is not supported");
		}
	};
	dropBoxScript.setAttribute("src", "https://www.dropbox.com/static/api/2/dropins.js");
	dropBoxScript.setAttribute("id", "dropboxjs");
	dropBoxScript.setAttribute("data-app-key", props.apiKey);
	document.head.appendChild(dropBoxScript);
}

function dropboxIconClicked() {
	if (window.Dropbox === undefined) {
		console.warn("VueDropboxPicker: Dropbox script not loaded");
		return;
	}

	let options: Dropbox.ChooserOptions = {
		success: async (files: Dropbox.ChooserFile[]) => {
			emits("picked", files);
		},

		cancel: function () {
			emits("cancel");
		},

		linkType: "direct",
		multiselect: props.multiselect,
		folderselect: props.folderselect,
	};

	if (props.sizeLimit) {
		options.sizeLimit = props.sizeLimit;
	}
	window.Dropbox.choose(options);
}
</script>
