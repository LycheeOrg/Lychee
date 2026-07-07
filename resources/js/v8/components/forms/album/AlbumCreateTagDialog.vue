<template>
	<UModal v-model:open="is_create_tag_album_visible" :dismissible="true">
		<template #body>
			<p class="mb-5">{{ $t("dialogs.new_tag_album.info") }}</p>
			<div class="inline-flex flex-col gap-3">
				<UFormField :label="$t('dialogs.new_tag_album.title')">
					<UInput id="title" v-model="title" class="w-full" />
				</UFormField>
				<UFormField :label="$t('dialogs.new_tag_album.set_tags')">
					<TagsInput v-model="tags" :add="false" />
				</UFormField>
				<USwitch
					v-model="is_and"
					class="my-2"
					:label="$t('gallery.album.properties.all_tags_must_match')"
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
							is_create_tag_album_visible = false;
						}
					"
				>
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" :disabled="!isValid" @click="create">
					{{ $t("dialogs.new_tag_album.create") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { trans } from "laravel-vue-i18n";
import TagsInput from "@/v8/components/forms/basic/TagsInput.vue";

const toast = useAppToast();
const router = useRouter();

const togglableStore = useTogglablesStateStore();
const { is_create_tag_album_visible } = storeToRefs(togglableStore);

const title = ref<string | undefined>(undefined);
const tags = ref<string[]>([]);
const is_and = ref<boolean>(true);

const isValid = computed(() => title.value !== undefined && title.value.length > 0 && title.value.length <= 100);

function create() {
	if (!isValid.value) {
		return;
	}

	AlbumService.createTag({
		title: title.value as string,
		tags: tags.value,
		is_and: is_and.value,
	})
		.then((response) => {
			is_create_tag_album_visible.value = false;
			AlbumService.clearAlbums();
			router.push(`/gallery/${response.data}`);
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: error.message });
		});
}
</script>
