<template>
	<Dialog
		v-model:visible="is_create_person_album_visible"
		pt:root:class="border-none"
		modal
		:dismissable-mask="true"
		@close="is_create_person_album_visible = false"
	>
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative w-full md:w-lg text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("dialogs.new_person_album.info") }}</p>
				<div class="inline-flex flex-col gap-3 px-9">
					<FloatLabel variant="on">
						<InputText id="title" v-model="title" />
						<label for="title">{{ $t("dialogs.new_person_album.title") }}</label>
					</FloatLabel>
					<FloatLabel variant="on">
						<Select
							v-model="selectedPersons"
							:options="availablePersons"
							option-label="name"
							option-value="id"
							filter
							:placeholder="$t('dialogs.new_person_album.set_persons')"
							class="w-full"
							multiple
						/>
						<label>{{ $t("dialogs.new_person_album.set_persons") }}</label>
					</FloatLabel>
					<div class="flex gap-2 items-center my-2">
						<ToggleSwitch v-model="is_and" input-id="pp_is_and" />
						<label for="pp_is_and" class="text-muted-color-emphasis">{{ $t("gallery.album.properties.all_persons_must_match") }}</label>
					</div>
				</div>
				<div class="flex items-center mt-9">
					<Button severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl" @click="closeCallback">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" :disabled="!isValid" @click="create">
						{{ $t("dialogs.new_person_album.create") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import PeopleService from "@/services/people-service";
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import Select from "primevue/select";
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import InputText from "@/components/forms/basic/InputText.vue";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { trans } from "laravel-vue-i18n";
import ToggleSwitch from "primevue/toggleswitch";

const toast = useToast();
const router = useRouter();

const togglableStore = useTogglablesStateStore();
const { is_create_person_album_visible } = storeToRefs(togglableStore);

const title = ref<string | undefined>(undefined);
const selectedPersons = ref<string[]>([]);
const is_and = ref<boolean>(false);
const availablePersons = ref<App.Http.Resources.Models.PersonResource[]>([]);

const isValid = computed(() => title.value !== undefined && title.value.length > 0 && title.value.length <= 100 && selectedPersons.value.length > 0);

onMounted(() => {
	loadPersons();
});

function loadPersons() {
	PeopleService.getPeople(1)
		.then((response) => {
			availablePersons.value = response.data.data;
		})
		.catch((error) => {
			console.error(error);
		});
}

function create() {
	if (!isValid.value) {
		return;
	}

	AlbumService.createPerson({
		title: title.value as string,
		persons: selectedPersons.value,
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
