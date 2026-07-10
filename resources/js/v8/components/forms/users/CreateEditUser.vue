<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<div class="w-full flex flex-col gap-2 justify-center">
				<UFormField :label="$t('users.username')">
					<UInput id="username" v-model="username" class="w-full" aria-label="Username" :autofocus="true" />
				</UFormField>
				<UFormField :label="$t('users.password')">
					<InputPassword id="password" v-model="password" aria-label="Password" />
				</UFormField>
				<UCheckbox v-model="may_upload" class="w-full" :label="$t('users.create_edit.upload_rights')" :ui="{ label: 'text-muted' }" />
				<UCheckbox
					v-model="may_edit_own_settings"
					class="w-full"
					:label="$t('users.create_edit.edit_rights')"
					:ui="{ label: 'text-muted' }"
				/>
				<UCheckbox
					v-if="is_se_enabled || is_se_preview_enabled"
					v-model="may_administrate"
					class="w-full"
					color="warning"
					:ui="{ label: 'text-muted' }"
				>
					<template #label> {{ $t("users.create_edit.admin_rights") }} <SETag /> </template>
				</UCheckbox>
				<div class="w-full flex items-center text-muted gap-2 pt-1">
					<label class="shrink-0">{{ $t("users.create_edit.upload_trust_level") }}</label>
					<USelectMenu v-model="uploadTrustLevelOption" :items="trustLevelOptions" label-key="label" class="w-full">
						<template #item-label="{ item }">{{ item.label }}</template>
					</USelectMenu>
				</div>
				<UCheckbox v-if="is_se_enabled || is_se_preview_enabled" v-model="has_quota" class="w-full" :ui="{ label: 'text-muted' }">
					<template #label>{{ $t("users.create_edit.quota") }} <SETag /></template>
				</UCheckbox>
				<div v-if="has_quota === true" class="w-full flex items-center gap-2 text-muted">
					<UInput id="quotaKb" v-model="quota_kb" class="w-1/2" aria-label="quotaKb" />
					<label class="pl-4 w-1/2" for="quotaKb">{{ $t("users.create_edit.quota_kb") }}</label>
				</div>
				<UFormField v-if="is_se_enabled" :label="$t('users.create_edit.note')" class="pt-2">
					<UTextarea id="note" v-model="noteForInput" class="w-full h-18" :rows="2" />
				</UFormField>
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
							visible = false;
						}
					"
				>
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton
					v-if="!props.isEdit"
					color="success"
					class="flex-1 justify-center font-bold"
					:disabled="username === undefined || password === undefined || username === '' || password === ''"
					@click="createUser"
				>
					<UIcon name="prime:user-plus" /><span class="hidden md:inline">{{ $t("users.create_edit.create") }}</span>
				</UButton>
				<UButton
					v-else
					color="neutral"
					class="flex-1 justify-center font-bold"
					:disabled="username === undefined || username === ''"
					@click="editUser"
				>
					<UIcon name="prime:user-edit" /><span class="hidden md:inline">{{ $t("users.create_edit.edit") }}</span>
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, Ref, ref, watch } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import UserManagementService from "@/services/user-management-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import SETag from "@/v8/components/icons/SETag.vue";
import UsersService from "@/services/users-service";
import { trans } from "laravel-vue-i18n";

const lycheeStore = useLycheeStateStore();
const { is_se_preview_enabled, is_se_enabled } = storeToRefs(lycheeStore);

type TrustLevelOption = { value: App.Enum.UserUploadTrustLevel; label: string };

const trustLevelOptions = computed<TrustLevelOption[]>(() => {
	if (is_se_enabled.value || is_se_preview_enabled.value) {
		return [
			{ value: "trusted", label: "Trusted" },
			{ value: "trust_but_verify", label: "Trust but Verify" },
			{ value: "monitor", label: "Monitor" },
			{ value: "check", label: "Check" },
		];
	} else {
		return [
			{ value: "trusted", label: "Trusted" },
			{ value: "check", label: "Check" },
		];
	}
});

const visible = defineModel("visible") as Ref<boolean>;
const props = defineProps<{
	user: App.Http.Resources.Models.UserManagementResource | undefined;
	isEdit: boolean;
}>();

const id = ref<number | undefined>(props.user?.id);
const username = ref<string | undefined>(props.user?.username);
const note = ref<string | undefined>(props.user?.note ?? undefined);
const password = ref<string | undefined>(undefined);
const may_edit_own_settings = ref(props.user?.may_edit_own_settings ?? false);
const may_administrate = ref(props.user?.may_administrate ?? false);
const may_upload = ref(props.user?.may_upload ?? false);
const has_quota = ref(props.user?.quota_kb !== undefined && props.user?.quota_kb !== null);
const quota_kb = ref(props.user?.quota_kb?.toString() ?? "0");
const upload_trust_level = ref<App.Enum.UserUploadTrustLevel>(props.user?.upload_trust_level ?? "trusted");

const uploadTrustLevelOption = computed<TrustLevelOption | undefined>({
	get: () => trustLevelOptions.value.find((o) => o.value === upload_trust_level.value),
	set: (v) => {
		if (v) upload_trust_level.value = v.value;
	},
});

// UTextarea's v-model requires `string | undefined` (no null).
const noteForInput = computed<string | undefined>({
	get: () => note.value ?? undefined,
	set: (v) => {
		note.value = v;
	},
});

const toast = useAppToast();
const emits = defineEmits<{
	refresh: [];
}>();

function createUser() {
	if (username.value === undefined || password.value === undefined) {
		return;
	}

	UserManagementService.create({
		username: username.value,
		password: password.value,
		may_edit_own_settings: may_edit_own_settings.value,
		may_administrate: may_administrate.value,
		may_upload: may_upload.value,
		upload_trust_level: upload_trust_level.value,
		has_quota: is_se_enabled ? has_quota.value : undefined,
		quota_kb: is_se_enabled ? parseInt(quota_kb.value) : undefined,
		note: is_se_enabled ? note.value : undefined,
	})
		.then(() => {
			visible.value = false;
			password.value = undefined;
			may_upload.value = false;
			may_administrate.value = false;
			may_edit_own_settings.value = false;
			username.value = undefined;
			has_quota.value = false;
			quota_kb.value = "0";
			upload_trust_level.value = "trusted";
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("users.user_created"), life: 3000 });
			emits("refresh");

			// Clear user count as it is cachable.
			UsersService.clearCount();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
		});
}

function editUser() {
	if (username.value === undefined || id.value === undefined) {
		return;
	}

	UserManagementService.edit({
		id: id.value,
		username: username.value,
		password: password.value,
		may_edit_own_settings: may_edit_own_settings.value,
		may_administrate: may_administrate.value,
		may_upload: may_upload.value,
		upload_trust_level: upload_trust_level.value,
		has_quota: is_se_enabled ? has_quota.value : undefined,
		quota_kb: is_se_enabled ? parseInt(quota_kb.value) : undefined,
		note: is_se_enabled ? note.value : undefined,
	})
		.then(() => {
			visible.value = false;
			password.value = undefined;
			may_upload.value = false;
			may_administrate.value = false;
			may_edit_own_settings.value = false;
			username.value = undefined;
			has_quota.value = false;
			quota_kb.value = "0";
			upload_trust_level.value = "trusted";
			toast.add({ severity: "success", summary: trans("users.change_saved"), detail: trans("users.user_updated"), life: 3000 });
			emits("refresh");
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
		});
}

watch(
	() => props.user,
	(newUser: App.Http.Resources.Models.UserManagementResource | undefined, _oldUser) => {
		id.value = newUser?.id;
		username.value = newUser?.username;
		may_edit_own_settings.value = newUser?.may_edit_own_settings ?? false;
		may_administrate.value = newUser?.may_administrate ?? false;
		may_upload.value = newUser?.may_upload ?? false;
		has_quota.value = newUser?.quota_kb !== undefined && newUser?.quota_kb !== null;
		quota_kb.value = newUser?.quota_kb?.toString() ?? "0";
		upload_trust_level.value = newUser?.upload_trust_level ?? "trusted";
	},
);
</script>
