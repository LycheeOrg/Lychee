<template>
	<Tag class="text-3xs rounded py-0.5" v-if="!is_se_info_hidden" v-tooltip="$t('dialogs.se.available')">SE</Tag>
</template>
<script lang="ts" setup>
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import Tag from "primevue/tag";

const lycheeStore = useLycheeStateStore();
lycheeStore.init();

const { is_se_info_hidden } = storeToRefs(lycheeStore);
</script>
