<template>
	<template v-if="url !== ''">
		<h2 class="text-muted-color-emphasis text-base font-bold mt-4 mb-1">
			{{ $t("gallery.photo.details.links.header") }}
		</h2>
		<div class="flex flex-col">
			<div class="flex gap-2 items-center" @click="copy(a)">
				<pre class="block text-xs overflow-x-scroll text-muted-color pt-2">{{ a }}</pre>
				<Button text class="py-0.5 shrink-0">{{ $t("gallery.photo.details.links.copy") }}</Button>
			</div>
			<div class="flex gap-2 items-center" @click="copy(bb)">
				<pre class="block text-xs overflow-x-scroll text-muted-color pt-2">{{ bb }}</pre>
				<Button text class="py-0.5 shrink-0">{{ $t("gallery.photo.details.links.copy") }}</Button>
			</div>
			<div class="flex gap-2 items-center" @click="copy(md)">
				<pre class="block text-xs overflow-x-scroll text-muted-color pt-2">{{ md }}</pre>
				<Button text class="py-0.5 shrink-0">{{ $t("gallery.photo.details.links.copy") }}</Button>
			</div>
		</div>
	</template>
</template>
<script setup lang="ts">
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { computed } from "vue";

const toast = useToast();

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource | undefined;
}>();

const url = computed(() => {
	const link = props.photo?.size_variants.medium?.url ?? props.photo?.size_variants.original?.url ?? "";
	if (link === "") {
		return "";
	}
	if (link.startsWith("http")) {
		return link;
	}
	return `https://${window.location.host}${link}`;
});

const a = computed(() => {
	return `<a href="${window.location.href}"  target="_blank"/><img src='${url.value}' /></a>`;
});
const bb = computed(() => {
	return `[url=${window.location.href}][img]${url.value}[/img][/url]`;
});
const md = computed(() => {
	return `[![](${url.value})](${window.location.href})`;
});

function copy(toClipBoard: string) {
	navigator.clipboard
		.writeText(toClipBoard)
		.then(() => toast.add({ severity: "info", summary: trans("gallery.photo.details.links.copy_success"), life: 3000 }));
}
</script>
<style scoped>
pre {
	-ms-overflow-style: none; /* Internet Explorer 10+ */
	scrollbar-width: none; /* Firefox, Safari 18.2+, Chromium 121+ */
}
pre::-webkit-scrollbar {
	display: none; /* Older Safari and Chromium */
}
</style>
