<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl w-full">
		<template #content>
			<div v-if="newOwner !== undefined">
				<p class="w-full mb-4 text-center text-muted-color-emphasis">
					{{ sprintf($t("dialogs.transfer.confirmation"), newOwner.username, props.album.title) }}<br />
					<span class="text-warning-700"><i class="pi pi-exclamation-triangle mr-2" />{{ $t("dialogs.transfer.lost_access_warning") }}</span
					><br />
					<span class="text-warning-700"><i class="pi pi-exclamation-triangle mr-2" />{{ $t("dialogs.transfer.warning") }}</span>
				</p>
			</div>
			<div v-else class="text-center w-full">
				<span class="font-bold">{{ $t("dialogs.transfer.query") }}</span>
				<SearchTargetUser :album="album" @selected="selected" />
			</div>
			<Button
				class="text-danger-800 font-bold hover:text-white hover:bg-danger-800 w-full bg-transparent border-none"
				:disabled="newOwner === undefined"
				@click="execute"
			>
				{{ $t("dialogs.transfer.transfer") }}
			</Button>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import Button from "primevue/button";
import Card from "primevue/card";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import SearchTargetUser from "@/components/forms/album/SearchTargetUser.vue";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource;
}>();

const router = useRouter();
const newOwner = ref<App.Http.Resources.Models.LightUserResource | undefined>(undefined);

function execute() {
	if (newOwner.value === undefined) {
		return;
	}
	AlbumService.transfer(props.album.id, newOwner.value.id).then(() => {
		router.push("/gallery");
		// @ts-expect-error
		AlbumService.clearCache(props.album?.parent_id);
	});
}

function selected(target: App.Http.Resources.Models.LightUserResource) {
	newOwner.value = target;
}
</script>
