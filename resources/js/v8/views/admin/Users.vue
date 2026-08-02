<template>
	<InviteUser v-model:visible="isInviteUserVisible" />
	<CreateEditUser v-model:visible="isCreateUserVisible" class="mt-10" :user="selectedUser" :is-edit="isEdit" @refresh="load" />
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("users.title") }}
	</UHeader>
	<USlideover v-model:open="isLegendOpen" side="right" class="w-sm">
		<template #header
			><span class="text-xl">{{ $t("users.legend") }}</span></template
		>
		<template #body>
			<ul class="text-muted flex flex-col gap-y-4">
				<li class="text-sm flex items-start gap-x-4">
					<UIcon name="lucide:upload" class="text-lg" />
					<span>{{ $t("users.upload_rights") }}</span>
				</li>
				<li class="text-sm flex items-start gap-x-4">
					<UIcon name="lucide:lock-open" class="text-xl" />
					<span>{{ $t("users.edit_rights") }}</span>
				</li>
				<li class="text-sm flex items-start gap-x-4">
					<UIcon name="lucide:shield" class="text-success text-xl -mt-1" />
					<span>{{ $t("users.upload_trust_level") }}</span>
				</li>
				<li v-if="is_se_enabled" class="text-sm flex items-start gap-x-4">
					<UIcon name="lucide:chart-pie" class="text-lg" />
					<span>{{ $t("users.quota") }}</span>
				</li>
			</ul>
		</template>
	</USlideover>

	<div class="max-w-3xl mx-auto p-4">
		<div class="flex items-start justify-between gap-4">
			<p class="text-highlighted">{{ $t("users.description") }}</p>
			<UButton
				icon="lucide:circle-help"
				color="neutral"
				variant="ghost"
				size="sm"
				:aria-label="$t('users.legend')"
				@click="isLegendOpen = true"
			/>
		</div>
		<div class="flex justify-end my-8 gap-2">
			<UButton variant="solid" color="warning" @click="inviteUser">{{ $t("users.invite.button") }}</UButton>
			<UButton variant="solid" color="success" @click="createUser">{{ $t("users.create") }}</UButton>
		</div>

		<UTable
			:data="users"
			:columns="columns"
			:loading="isLoadingUsers"
			sticky
			:virtualize="{ estimateSize: 29, overscan: 12 }"
			:ui="{ base: 'table-fixed', td: 'px-4 py-1' }"
			class="max-h-[65vh]"
		>
			<template #username-cell="{ row }">
				<span class="flex items-center gap-1">
					{{ row.original.username }}
					<UTooltip v-if="row.original.may_administrate" :text="$t(row.original.is_owner ? 'users.line.owner' : 'users.line.admin')">
						<UIcon name="lucide:crown" :class="row.original.is_owner ? 'text-red-600' : 'text-orange-400'" />
					</UTooltip>
				</span>
			</template>

			<template #may_upload-header>
				<UTooltip :text="$t('users.upload_rights')">
					<UIcon name="lucide:upload" />
				</UTooltip>
			</template>
			<template #may_upload-cell="{ row }">
				<UIcon
					:name="row.original.may_upload ? 'lucide:check' : 'lucide:x'"
					:class="row.original.may_upload ? 'text-success' : 'text-muted opacity-30'"
				/>
			</template>

			<template #may_edit_own_settings-header>
				<UTooltip :text="$t('users.edit_rights')">
					<UIcon name="lucide:lock-open" />
				</UTooltip>
			</template>
			<template #may_edit_own_settings-cell="{ row }">
				<UIcon
					:name="row.original.may_edit_own_settings ? 'lucide:check' : 'lucide:x'"
					:class="row.original.may_edit_own_settings ? 'text-success' : 'text-muted opacity-30'"
				/>
			</template>

			<template #upload_trust_level-header>
				<UTooltip :text="$t('users.upload_trust_level')">
					<UIcon name="lucide:shield" />
				</UTooltip>
			</template>
			<template #upload_trust_level-cell="{ row }">
				<UTooltip :text="trustLevelInfo(row.original.upload_trust_level).text">
					<UIcon name="lucide:shield" :class="trustLevelInfo(row.original.upload_trust_level).class" />
				</UTooltip>
			</template>

			<template #quota-header>
				<UTooltip :text="$t('users.quota')">
					<UIcon name="lucide:chart-pie" class="text-lg" />
				</UTooltip>
			</template>
			<template #quota-cell="{ row }">
				<UTooltip v-if="row.original.quota_kb !== null" :text="formattedQuota(row.original)">
					<UIcon name="lucide:chart-pie" class="text-lg" />
				</UTooltip>
			</template>

			<template #space-cell="{ row }">
				<div v-if="row.original.space !== null && spaceRatio(row.original) > 0">
					<UProgress :model-value="spaceRatio(row.original)" :color="meterColor(row.original)" class="w-full min-w-16" />
					<span dir="ltr">{{ formattedSpace(row.original) }}</span>
				</div>
			</template>

			<template #actions-cell="{ row }">
				<div class="flex justify-end">
					<UButton
						color="neutral"
						:disabled="row.original.is_owner"
						variant="ghost"
						icon="lucide:user-pen"
						:label="$t('users.line.edit')"
						@click="editUser(row.original.id)"
					/>
					<UButton
						color="error"
						variant="ghost"
						:disabled="row.original.is_owner"
						icon="lucide:user-minus"
						:label="$t('users.line.delete')"
						@click="deleteUser(row.original.id)"
					/>
				</div>
			</template>
		</UTable>
	</div>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { storeToRefs } from "pinia";
import CreateEditUser from "@/v8/components/forms/users/CreateEditUser.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import UserManagementService from "@/services/user-management-service";
import UsersService from "@/services/users-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { trans } from "laravel-vue-i18n";
import InviteUser from "@/v8/components/modals/InviteUser.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useSizeVariantStats } from "@/v8/composables/useSizeVariantStats";
import UButton from "@nuxt/ui/components/Button.vue";
import UTooltip from "@nuxt/ui/components/Tooltip.vue";
import UProgress from "@nuxt/ui/components/Progress.vue";
import type { TableColumn } from "@nuxt/ui";

type User = App.Http.Resources.Models.UserManagementResource;

const { sizeToUnit } = useSizeVariantStats();

const lycheeStore = useLycheeStateStore();
lycheeStore.load();
const { is_se_enabled } = storeToRefs(lycheeStore);

const users = ref<User[]>([]);
const isLoadingUsers = ref(true);
const isCreateUserVisible = ref(false);
const isInviteUserVisible = ref(false);
const isLegendOpen = ref(false);
const totalUsedSpace = ref(0);

const toast = useAppToast();

const selectedUser = ref<User | undefined>(undefined);
const isEdit = ref(false);
const isQuotaEnabled = computed(() => is_se_enabled.value && users.value.reduce((acc, user) => acc || user.quota_kb !== null, false));

function spaceRatio(user: User): number {
	if (user.quota_kb !== null) {
		return ((user.space ?? 0) * 100) / (user.quota_kb * 1024);
	}
	return ((user.space ?? 0) * 100) / (totalUsedSpace.value || 1);
}

function formattedQuota(user: User): string {
	return user.quota_kb !== null ? sizeToUnit(user.quota_kb * 1024) : "";
}

function formattedSpace(user: User): string {
	if (user.quota_kb !== null) {
		return `${sizeToUnit(user.space ?? 0)} / ${sizeToUnit(user.quota_kb * 1024)}`;
	}
	return user.space !== null ? sizeToUnit(user.space) : "";
}

function meterColor(user: User): "error" | "warning" | "success" | "primary" {
	const ratio = spaceRatio(user);
	if (user.quota_kb !== null) {
		if (ratio > 80) {
			return "warning";
		}
		return ratio > 90 ? "error" : "success";
	}
	return ratio > 90 ? "error" : "primary";
}

function trustLevelInfo(level: string): { text: string; class: string } {
	const map: Record<string, { text: string; class: string }> = {
		trusted: { text: trans("users.create_edit.upload_trust_level_trusted"), class: "text-success" },
		trust_but_verify: { text: trans("users.create_edit.upload_trust_level_trust_but_verify"), class: "text-blue-500" },
		monitor: { text: trans("users.create_edit.upload_trust_level_monitor"), class: "text-yellow-500" },
	};
	return map[level] ?? { text: trans("users.create_edit.upload_trust_level_check"), class: "text-error" };
}

const columns = computed<TableColumn<User>[]>(() => {
	const cols: TableColumn<User>[] = [
		{ accessorKey: "username", header: trans("users.username") },
		{ id: "may_upload" },
		{ id: "may_edit_own_settings" },
		{ id: "upload_trust_level" },
	];

	if (isQuotaEnabled.value) {
		cols.push({ id: "quota" });
	}

	cols.push({ id: "space" }, { id: "actions" });

	return cols;
});

function load() {
	isLoadingUsers.value = true;
	UserManagementService.get()
		.then((response) => {
			users.value = response.data;
			totalUsedSpace.value = response.data.reduce((acc, user) => acc + (user.space ?? 0), 0);
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			isLoadingUsers.value = false;
		});
}

function inviteUser() {
	isInviteUserVisible.value = true;
}

function deleteUser(id: number) {
	UserManagementService.delete({ id: id }).then(() => {
		toast.add({ severity: "success", summary: "Success", detail: trans("users.user_deleted") });

		// Clear user count as it is cachable.
		UsersService.clearCount();

		load();
	});
}

function editUser(id: number) {
	selectedUser.value = users.value.find((user) => user.id === id);
	isEdit.value = true;
	isCreateUserVisible.value = true;
}

function createUser() {
	selectedUser.value = undefined;
	isEdit.value = false;
	isCreateUserVisible.value = true;
}

load();
</script>
