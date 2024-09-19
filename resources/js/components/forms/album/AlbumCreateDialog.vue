<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative max-w-full text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("lychee.TITLE_NEW_ALBUM") }}</p>
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel>
						<InputText id="title" v-model="title" />
						<label class="" for="title">{{ $t("lychee.ALBUM_SET_TITLE") }}</label>
					</FloatLabel>
				</div>
				<div class="flex items-center mt-9">
					<Button
						@click="closeCallback"
						text
						class="p-3 w-full font-bold border-none text-muted-color hover:text-danger-700 rounded-bl-xl flex-shrink-2"
						>{{ $t("lychee.CANCEL") }}</Button
					>
					<Button
						@click="create"
						text
						class="p-3 w-full font-bold border-none text-primary-500 hover:bg-primary-500 hover:text-surface-0 rounded-none rounded-br-xl flex-shrink"
						>{{ $t("lychee.CREATE_ALBUM") }}</Button
					>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import Dialog from "primevue/dialog";
import InputText from "@/components/forms/basic/InputText.vue";
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";

const props = defineProps<{
	parentId: string | null;
}>();

const visible = defineModel("visible", { default: false });
const parentId = ref(props.parentId);

const router = useRouter();

const title = ref(undefined as undefined | string);

function create() {
	if (!title.value) {
		return;
	}

	AlbumService.createAlbum({
		title: title.value,
		parent_id: parentId.value,
	}).then((response) => {
		visible.value = false;
		router.push(`/gallery/${response.data}`);
	});
}

watch(
	() => props.parentId,
	(newAlbumID, _oldAlbumID) => {
		parentId.value = newAlbumID as string | null;
	},
);
</script>
<!-- <div>
    <div class="p-9">
        <p class="mb-5 text-text-main-200 text-sm/4">{{ __('lychee.TITLE_NEW_ALBUM') }}</p>
        <form>
            <div class="my-3 first:mt-0 last:mb-0">
                <x-forms.inputs.text class="w-full" autocapitalize="off" wire:model="title"
                    x-intersect="$el.focus()"
                    placeholder="{{ __('lychee.UNTITLED') }}" :has_error="$errors->has('title')" />
            </div>
        </form>
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full"
            wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
        <x-forms.buttons.action class="border-t border-t-bg-800 rounded-br-md w-full"
            @keydown.enter.window="$wire.submit()"
            wire:click="submit">{{ __('lychee.CREATE_ALBUM') }}</x-forms.buttons.action>
    </div>
</div> -->
