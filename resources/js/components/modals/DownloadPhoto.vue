<template>
	<Dialog
		v-model:visible="visible"
		modal
		:pt="{
			root: '!border-none',
			mask: {
				style: 'backdrop-filter: blur(2px)',
			},
		}"
		@hide="closeCallback"
	>
		<template #container="{ closeCallback }">
			<div class="flex flex-col relative w-[500px] text-sm rounded-md text-muted-color">
				<div class="flex flex-wrap gap-0.5 justify-center align-top text-text-main-0/80 p-9">
					<template v-for="sv in props.photo.size_variants">
						<Button text v-if="sv?.locale" class="w-full"
							><i class="pi pi-cloud-download"></i> {{ sv?.locale }} - {{ sv?.width }}x{{ sv?.height }} ({{ sv?.filesize }})
						</Button>
					</template>
				</div>
				<div class="flex justify-center">
					<Button @click="closeCallback" text class="p-3 w-full font-bold border-1 border-white-alpha-30 hover:bg-white-alpha-10">
						{{ $t("lychee.CLOSE") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const visible = defineModel("visible", { default: false });

function closeCallback() {
	visible.value = false;
}
</script>
