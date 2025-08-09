<template>
	<Select
		id="targetUser"
		v-model="selectedTarget"
		class="w-full border-none"
		filter
		:placeholder="$t('dialogs.target_user.placeholder')"
		:loading="options === undefined"
		:options="options"
		option-label="name"
		show-clear
		@update:model-value="selected"
	>
		<template #value="slotProps">
			<div v-if="slotProps.value" class="flex items-center">
				<div>
					<i v-if="slotProps.value.type === 'group'" class="pi pi-users ltr:mr-1 rtl:ml-1" />
					{{ $t(slotProps.value.name) }}
				</div>
			</div>
		</template>
		<template #option="slotProps">
			<div class="flex items-center">
				<span>
					<i v-if="slotProps.option.type === 'group'" class="pi pi-users ltr:mr-1 rtl:ml-1" />
					{{ slotProps.option.name }}
				</span>
			</div>
		</template>
	</Select>
</template>
<script setup lang="ts">
import { onMounted, watch } from "vue";
import Select from "primevue/select";
import { type UserOrGroup, type UserOrGroupId, useSearchUserGroupComputed } from "@/composables/search/searchUserGroupComputed";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { is_se_enabled } = storeToRefs(lycheeStore);

const props = defineProps<{
	filteredUsersIds?: UserOrGroupId[];
	withGroups?: boolean;
}>();

const emits = defineEmits<{
	selected: [user: UserOrGroup];
	"no-target": [];
}>();

const { options, selectedTarget, load, selected, filterUserGroups } = useSearchUserGroupComputed(is_se_enabled, emits);

onMounted(load);

watch(
	() => props.filteredUsersIds,
	(v) => filterUserGroups(v),
);
</script>
