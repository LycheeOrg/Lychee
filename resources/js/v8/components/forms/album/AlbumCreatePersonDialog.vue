<template>
	<UModal v-model:open="is_create_person_album_visible" :dismissible="true">
		<template #body>
			<p class="mb-5">{{ $t("dialogs.new_person_album.info") }}</p>
			<div class="inline-flex flex-col gap-3">
				<UFormField :label="$t('dialogs.new_person_album.title')">
					<UInput id="title" v-model="title" class="w-full" />
				</UFormField>
				<UFormField :label="$t('dialogs.new_person_album.set_persons')">
					<PersonsInput v-model="selectedPersons" />
				</UFormField>
				<USwitch
					v-model="is_and"
					class="my-2"
					:label="$t('gallery.album.properties.all_persons_must_match')"
					:ui="{ label: 'text-highlighted' }"
				/>
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton
					color="neutral"
					variant="soft"
					class="flex-1 justify-center font-bold"
					@click="
						() => {
							is_create_person_album_visible = false;
						}
					"
				>
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" :disabled="!isValid" @click="create">
					{{ $t("dialogs.new_person_album.create") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import PersonsInput from "@/v8/components/forms/basic/PersonsInput.vue";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { trans } from "laravel-vue-i18n";

const toast = useAppToast();
const router = useRouter();

const togglableStore = useTogglablesStateStore();
const { is_create_person_album_visible } = storeToRefs(togglableStore);

const title = ref<string | undefined>(undefined);
const selectedPersons = ref<App.Http.Resources.Models.PersonResource[]>([]);
const is_and = ref<boolean>(false);

const isValid = computed(() => title.value !== undefined && title.value.length > 0 && title.value.length <= 100 && selectedPersons.value.length > 0);

function create() {
	if (!isValid.value) {
		return;
	}

	AlbumService.createPerson({
		title: title.value as string,
		persons: selectedPersons.value.map((p) => p.id),
		is_and: is_and.value,
	})
		.then((response) => {
			is_create_person_album_visible.value = false;
			AlbumService.clearAlbums();
			router.push(`/gallery/${response.data}`);
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: error.message });
		});
}
</script>
