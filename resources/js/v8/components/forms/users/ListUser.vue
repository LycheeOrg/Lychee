<template>
	<div class="w-full flex flex-wrap md:flex-nowrap items-center">
		<div class="w-9/12 lg:w-8/12 flex flex-wrap">
			<div class="w-2/3 flex items-center gap-1">
				{{ props.user.username }}
				<UTooltip v-if="props.user.may_administrate && props.user.is_owner" :text="$t('users.line.owner')">
					<UIcon name="lucide:crown" class="text-red-600" />
				</UTooltip>
				<UTooltip v-else-if="props.user.may_administrate" :text="$t('users.line.admin')">
					<UIcon name="lucide:crown" class="text-orange-400" />
				</UTooltip>
			</div>
			<div class="w-1/3 flex items-center justify-evenly">
				<div class="w-full text-center">
					<UIcon v-if="props.user.may_upload" name="lucide:check" class="text-success" />
					<UIcon v-else name="lucide:x" class="text-muted opacity-30" />
				</div>
				<div class="w-full text-center">
					<UIcon v-if="props.user.may_edit_own_settings" name="lucide:check" class="text-success" />
					<UIcon v-else name="lucide:x" class="text-muted opacity-30" />
				</div>
				<div class="w-full text-center">
					<UTooltip v-if="props.user.upload_trust_level === 'trusted'" :text="$t('users.create_edit.upload_trust_level_trusted')">
						<UIcon name="lucide:shield" class="text-success" />
					</UTooltip>
					<UTooltip
						v-else-if="props.user.upload_trust_level === 'trust_but_verify'"
						:text="$t('users.create_edit.upload_trust_level_trust_but_verify')"
					>
						<UIcon name="lucide:shield" class="text-blue-500" />
					</UTooltip>
					<UTooltip v-else-if="props.user.upload_trust_level === 'monitor'" :text="$t('users.create_edit.upload_trust_level_monitor')">
						<UIcon name="lucide:shield" class="text-yellow-500" />
					</UTooltip>
					<UTooltip v-else :text="$t('users.create_edit.upload_trust_level_check')">
						<UIcon name="lucide:shield" class="text-error" />
					</UTooltip>
				</div>
				<div v-if="isQuotaEnabled" class="w-full text-center">
					<UTooltip v-if="props.user.quota_kb !== null" :text="formattedQuota">
						<UIcon name="lucide:chart-pie" class="text-muted" />
					</UTooltip>
				</div>
			</div>
			<template v-if="showMetterBar">
				<UTooltip v-if="value > 0" :text="formattedSpace">
					<UProgress :model-value="value" class="w-full mt-1.5 mb-3.5" :color="meterColor" />
				</UTooltip>
			</template>
		</div>
		<UButton color="neutral" class="w-1/12 lg:w-2/12 justify-center" :disabled="props.user.is_owner" @click="editUser">
			<UIcon name="lucide:user-pen" /><span class="hidden md:inline">{{ $t("users.line.edit") }}</span>
		</UButton>
		<UButton color="error" variant="ghost" class="w-1/12 lg:w-2/12 justify-center" :disabled="props.user.is_owner" @click="deleteUser">
			<UIcon name="lucide:user-minus" /><span class="hidden md:inline">{{ $t("users.line.delete") }}</span>
		</UButton>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { sizeToUnit } from "@/utils/StatsSizeVariantToColours";

const props = defineProps<{
	user: App.Http.Resources.Models.UserManagementResource;
	totalUsedSpace: number;
	isQuotaEnabled: boolean;
}>();

const value = computed(() => {
	if (props.user.quota_kb !== null) {
		return ((props.user.space ?? 0) * 100) / (props.user.quota_kb * 1024);
	}
	return ((props.user.space ?? 0) * 100) / (props.totalUsedSpace ?? 1);
});

const formattedQuota = computed(() => {
	if (props.user.quota_kb !== null) {
		return `${sizeToUnit(props.user.quota_kb * 1024)}`;
	}
	return "";
});

const formattedSpace = computed(() => {
	if (props.user.quota_kb !== null) {
		return `${sizeToUnit(props.user.space ?? 0)} / ${sizeToUnit(props.user.quota_kb * 1024)}`;
	}
	return props.user.space !== null ? sizeToUnit(props.user.space) : "";
});

const meterColor = computed<"error" | "warning" | "success" | "primary">(() => {
	if (props.user.quota_kb !== null && value.value > 100) {
		return "error";
	}
	if (props.user.quota_kb !== null && value.value > 80) {
		return "warning";
	}
	if (props.user.quota_kb !== null && value.value > 60) {
		return "warning";
	}
	if (props.user.quota_kb !== null) {
		return "success";
	}
	return value.value > 100 ? "error" : "primary";
});

const showMetterBar = computed(() => {
	return props.user.space !== null;
});

const id = ref(props.user.id);

const emits = defineEmits<{
	deleteUser: [id: number];
	editUser: [id: number];
}>();

function deleteUser() {
	emits("deleteUser", id.value);
}

function editUser() {
	emits("editUser", id.value);
}

watch(
	() => props.user,
	(newUser: App.Http.Resources.Models.UserManagementResource, _oldUser) => {
		id.value = newUser.id;
	},
);
</script>
