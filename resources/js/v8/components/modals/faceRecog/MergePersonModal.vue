<template>
	<UModal v-model:open="visible" :dismissible="true" @update:open="(v) => v && onShow()">
		<template #header>
			<span class="font-bold">{{ $t("people.merge.title") }}</span>
		</template>
		<template #body>
			<div class="flex flex-col gap-4">
				<!-- Source person info -->
				<div class="flex items-center gap-3 p-3 rounded-lg bg-elevated">
					<img
						v-if="sourcePerson.representative_crop_url"
						:src="sourcePerson.representative_crop_url"
						:alt="sourcePerson.name"
						class="w-10 h-10 rounded-full object-cover shrink-0"
					/>
					<UIcon v-else name="prime:user" class="w-10 h-10 flex items-center justify-center text-xl text-muted shrink-0" />
					<div>
						<div class="font-semibold">{{ sourcePerson.name }}</div>
						<div class="text-xs text-muted">{{ sourcePerson.face_count }} {{ $t("people.faces_label") }}</div>
					</div>
				</div>

				<div class="text-center text-sm text-muted">{{ $t("people.merge.into") }}</div>

				<!-- Target person dropdown -->
				<USelectMenu
					v-model="targetPersonId"
					:items="targetPeople"
					value-key="id"
					label-key="name"
					:placeholder="$t('people.merge.select_target')"
					class="w-full"
					:loading="loadingPeople"
				>
					<template #item="{ item }">
						<div class="flex items-center gap-2">
							<img
								v-if="item.representative_crop_url"
								:src="item.representative_crop_url"
								class="w-6 h-6 rounded-full object-cover shrink-0"
								alt=""
							/>
							<UIcon v-else name="prime:user" class="w-6 h-6 flex items-center justify-center text-muted shrink-0" />
							<span class="flex-1 truncate">{{ item.name }}</span>
							<span class="text-xs text-muted shrink-0">{{ item.face_count }}</span>
						</div>
					</template>
				</USelectMenu>

				<!-- Warning -->
				<div class="text-xs text-warning flex items-start gap-2">
					<UIcon name="prime:exclamation-triangle" class="mt-0.5 shrink-0" />
					<span>{{ $t("people.merge.warning") }}</span>
				</div>
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" :label="$t('gallery.cancel')" color="neutral" variant="soft" @click="visible = false" />
				<UButton
					class="flex-1 justify-center"
					:label="$t('people.merge.confirm')"
					color="error"
					:disabled="!targetPersonId"
					:loading="submitting"
					@click="submit"
				/>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import PeopleService from "@/services/people-service";

const props = defineProps<{
	sourcePerson: App.Http.Resources.Models.PersonResource;
}>();

const emits = defineEmits<{
	merged: [targetPersonId: string];
}>();

const visible = defineModel<boolean>("visible", { default: false });
const toast = useAppToast();
const people = ref<App.Http.Resources.Models.PersonResource[]>([]);
const loadingPeople = ref(false);
const targetPersonId = ref<string | undefined>(undefined);
const submitting = ref(false);

const targetPeople = computed(() => people.value.filter((p) => p.id !== props.sourcePerson.id));

function loadPeople() {
	loadingPeople.value = true;
	PeopleService.getPeople()
		.then((response) => {
			people.value = response.data.data;
		})
		.finally(() => {
			loadingPeople.value = false;
		});
}

function submit() {
	if (!targetPersonId.value) {
		return;
	}
	submitting.value = true;
	PeopleService.merge(targetPersonId.value, props.sourcePerson.id)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.merge.success"), life: 3000 });
			visible.value = false;
			emits("merged", targetPersonId.value as string);
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			submitting.value = false;
		});
}

function onShow() {
	targetPersonId.value = undefined;
	loadPeople();
}
</script>
