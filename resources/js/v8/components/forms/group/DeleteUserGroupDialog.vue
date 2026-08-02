<template>
	<UModal v-model:open="visible" :title="$t('user-groups.confirm_delete_header')" :dismissible="true">
		<template #body>
			<p class="text-center text-muted text-wrap">
				{{ $t("user-groups.confirm_delete_message") }}
			</p>
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
					{{ $t("user-groups.cancel") }}
				</UButton>
				<UButton color="error" variant="solid" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("user-groups.delete") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { UserGroupService } from "@/services/user-group-service";

const props = defineProps<{
	group?: App.Http.Resources.Models.UserGroupResource;
}>();

const visible = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{
	deleted: [];
}>();

function execute() {
	visible.value = false;

	if (props.group === undefined) {
		return;
	}

	UserGroupService.deleteUserGroup(props.group.id).then(() => {
		emits("deleted");
	});
}
</script>
