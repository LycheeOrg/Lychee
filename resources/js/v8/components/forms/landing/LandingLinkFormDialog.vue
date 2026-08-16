<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #header>
			<span class="font-bold text-xl">{{ link ? $t("landing_link.modal_edit_title") : $t("landing_link.modal_create_title") }}</span>
		</template>
		<template #body>
			<form @submit.prevent="save">
				<div class="flex flex-col gap-6">
					<UFormField :label="$t('landing_link.field_label')" required>
						<UInput id="ll_label" v-model="form.label" class="w-full" required />
					</UFormField>

					<UFormField :label="$t('landing_link.field_url')" :hint="isBuiltIn ? $t('landing_link.built_in_url_hint') : undefined" required>
						<UInput id="ll_url" v-model="form.url" class="w-full" type="url" required :disabled="isBuiltIn" />
					</UFormField>

					<div class="flex items-center gap-4">
						<label class="font-semibold w-1/3 ltr:text-left rtl:text-right">{{ $t("landing_link.field_placement") }}</label>
						<USelectMenu v-model="selectedPlacement" :items="placementOptions" label-key="label" class="w-2/3">
							<template #item-label="{ item }">{{ item.label }}</template>
						</USelectMenu>
					</div>

					<USwitch
						v-model="form.open_in_new_tab"
						class="ltr:text-left rtl:text-right"
						:label="$t('landing_link.field_open_in_new_tab')"
						:ui="{ label: 'font-semibold' }"
					/>
					<USwitch
						v-model="form.enabled"
						class="ltr:text-left rtl:text-right"
						:label="$t('landing_link.field_enabled')"
						:ui="{ label: 'font-semibold' }"
					/>
				</div>
			</form>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton :label="$t('landing_link.cancel')" color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="onHide" />
				<UButton
					:label="link ? $t('landing_link.save') : $t('landing_link.create')"
					color="primary"
					variant="solid"
					class="flex-1 justify-center font-bold"
					:loading="isSaving"
					@click="save"
				/>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import LandingLinkService, { type CreateLandingLinkRequest } from "@/services/landing-link-service";

const props = defineProps<{
	link?: App.Http.Resources.Models.LandingLinkResource;
	nextSortOrder: number;
}>();

const visible = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{
	saved: [];
}>();

const toast = useAppToast();
const isSaving = ref(false);

function defaultForm(): CreateLandingLinkRequest {
	return {
		label: props.link?.label ?? "",
		url: props.link?.url ?? "",
		placement: props.link?.placement ?? "nav",
		open_in_new_tab: props.link?.open_in_new_tab ?? true,
		sort_order: props.link?.sort_order ?? props.nextSortOrder,
		enabled: props.link?.enabled ?? true,
	};
}

const form = ref<CreateLandingLinkRequest>(defaultForm());

const isBuiltIn = computed(() => props.link?.is_built_in ?? false);

type PlacementOption = { label: string; value: App.Enum.LandingLinkPlacement };

const placementOptions: PlacementOption[] = [
	{ label: trans("landing_link.placement_nav"), value: "nav" },
	{ label: trans("landing_link.placement_footer"), value: "footer" },
	{ label: trans("landing_link.placement_both"), value: "both" },
];

const selectedPlacement = computed<PlacementOption | undefined>({
	get: () => placementOptions.find((o) => o.value === form.value.placement),
	set: (v) => {
		if (v) form.value.placement = v.value;
	},
});

function onHide(): void {
	visible.value = false;
}

function save(): void {
	isSaving.value = true;

	let request;
	if (!props.link) {
		request = LandingLinkService.create(form.value);
	} else if (isBuiltIn.value) {
		// The URL of a built-in link is a route name, not editable here, and
		// must never be resubmitted - the backend rejects any change to it.
		request = LandingLinkService.patch(props.link.id, {
			landing_link_id: props.link.id,
			label: form.value.label,
			placement: form.value.placement,
			open_in_new_tab: form.value.open_in_new_tab,
			enabled: form.value.enabled,
		});
	} else {
		request = LandingLinkService.update(props.link.id, { ...form.value, landing_link_id: props.link.id });
	}

	request
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans(props.link ? "landing_link.updated" : "landing_link.created"),
				life: 3000,
			});
			visible.value = false;
			emits("saved");
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_link.error_save"), life: 3000 });
		})
		.finally(() => {
			isSaving.value = false;
		});
}

watch(visible, (isOpen) => {
	if (isOpen) {
		form.value = defaultForm();
	}
});
</script>
