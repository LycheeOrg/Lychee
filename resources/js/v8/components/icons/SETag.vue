<template>
	<UTooltip v-if="!is_se_info_hidden" :text="$t('dialogs.se.available')">
		<UBadge class="text-3xs rounded py-0.5" color="primary" variant="subtle">SE</UBadge>
	</UTooltip>
</template>
<script lang="ts" setup>
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
lycheeStore.load();

const { is_se_info_hidden } = storeToRefs(lycheeStore);
</script>
