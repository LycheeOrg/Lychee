<template>
	<div class="w-full flex flex-wrap md:flex-nowrap gap-2 justify-center items-center">
		<div class="w-3/6 flex flex-wrap">
			<div class="w-2/3">
				{{ props.user.username }}
			</div>
			<div class="w-1/6 flex justify-center items-center">
				<i v-if="props.user.may_upload" class="pi pi-check text-create-600"></i>
				<i v-else class="pi pi-times text-muted-color"></i>
			</div>
			<div class="w-1/6 flex justify-center items-center">
				<i v-if="props.user.may_edit_own_settings" class="pi pi-check text-create-600"></i>
				<i v-else class="pi pi-times text-muted-color"></i>
			</div>
			<template v-if="showMetterBar">
				<ProgressBar
					v-if="value > 0"
					:value="value"
					:show-value="false"
					class="w-full mt-4"
					:pt:value:class="colorMetterBar"
					v-tooltip.bottom="formattedSpace"
				/>
			</template>
		</div>
		<Button @click="deleteUser" class="border-0 bg-surface text-danger-600 hover:bg-danger-700 hover:text-white w-1/6">
			<i class="pi pi-user-minus" /><span class="hidden md:inline">{{ $t("lychee.DELETE") }}</span></Button
		>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import Button from "primevue/button";
import ProgressBar from "primevue/progressbar";
import { sizeToUnit } from "@/utils/StatsSizeVariantToColours";

const props = defineProps<{
	user: App.Http.Resources.Models.UserManagementResource;
	totalUsedSpace: number;
}>();

const value = computed(() => {
	if (props.user.quota_kb !== null) {
		return ((props.user.space ?? 0) * 100) / props.user.quota_kb;
	}
	return ((props.user.space ?? 0) * 100) / (props.totalUsedSpace ?? 1);
});

const formattedSpace = computed(() => {
	if (props.user.quota_kb !== null) {
		return `${sizeToUnit(props.user.space ?? 0)} / ${sizeToUnit(props.user.quota_kb * 1024)}`;
	}
	return props.user.space !== null ? sizeToUnit(props.user.space) : "";
});

const colorMetterBar = computed(() => {
	if (props.user.quota_kb !== null && value.value > 100) {
		return "bg-danger-600";
	}
	if (props.user.quota_kb !== null && value.value > 80) {
		return "bg-orange-500";
	}
	if (props.user.quota_kb !== null && value.value > 60) {
		return "bg-yellow-500";
	}
	if (props.user.quota_kb !== null) {
		return "bg-create-600";
	}
	return value.value > 100 ? "bg-danger-600" : "bg-info";
});

const showMetterBar = computed(() => {
	return props.user.space !== null;
});

const id = ref(props.user.id);

const emits = defineEmits<{
	deleteUser: [id: number];
}>();

function deleteUser() {
	emits("deleteUser", id.value);
}

watch(
	() => props.user,
	(newUser: App.Http.Resources.Models.UserManagementResource, _oldUser) => {
		id.value = newUser.id;
	},
);
</script>
