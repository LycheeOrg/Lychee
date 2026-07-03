<template>
	<UCard class="sm:p-4 xl:px-9 max-w-3xl w-full">
		<div v-if="newOwner !== undefined">
			<p class="w-full mb-4 text-center text-highlighted">
				{{ sprintf($t("dialogs.transfer.confirmation"), newOwner.name, albumStore.album?.title) }}<br />
				<span class="text-warning">
					<UIcon name="prime:exclamation-triangle" class="ltr:mr-2 rtl:ml-2" />
					{{ $t("dialogs.transfer.lost_access_warning") }} </span
				><br />
				<span class="text-warning">
					<UIcon name="prime:exclamation-triangle" class="ltr:mr-2 rtl:ml-2" />
					{{ $t("dialogs.transfer.warning") }}
				</span>
			</p>
		</div>
		<div v-else class="text-center w-full">
			<span class="font-bold">{{ $t("dialogs.transfer.query") }}</span>
			<SearchTargetUser :with-groups="false" @selected="selected" />
		</div>
		<UButton color="error" variant="ghost" class="font-bold w-full justify-center" :disabled="newOwner === undefined" @click="execute">
			{{ $t("dialogs.transfer.transfer") }}
		</UButton>
	</UCard>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import SearchTargetUser from "@/v8/components/forms/album/SearchTargetUser.vue";
import { type UserOrGroup } from "@/stores/UsersAndGroupsState";
import { useAlbumStore } from "@/stores/AlbumState";

const albumStore = useAlbumStore();
const router = useRouter();
const newOwner = ref<UserOrGroup | undefined>(undefined);

function execute() {
	if (albumStore.album === undefined) {
		return;
	}
	if (newOwner.value === undefined) {
		return;
	}
	AlbumService.transfer(albumStore.album.id, newOwner.value.id).then(() => {
		router.push("/gallery");
		AlbumService.clearCache(albumStore.modelAlbum?.parent_id);
	});
}

function selected(target: UserOrGroup) {
	newOwner.value = target;
}
</script>
