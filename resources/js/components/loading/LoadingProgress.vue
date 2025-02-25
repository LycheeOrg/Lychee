<template>
	<ProgressBar v-if="isLoadingDesktop" mode="indeterminate" class="rounded-none absolute w-full" :pt:value:class="'rounded-none'" />
	<ProgressSpinner v-if="isLoadingMobile" class="left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 absolute z-10" />
</template>
<script setup lang="ts">
import { computed, type Ref } from "vue";
import { isTouchDevice } from "@/utils/keybindings-utils";
import ProgressBar from "primevue/progressbar";
import ProgressSpinner from "primevue/progressspinner";

const loading = defineModel("loading") as Ref<boolean>;

const isLoadingMobile = computed(() => isTouchDevice() && loading.value);
const isLoadingDesktop = computed(() => !isTouchDevice() && loading.value);
</script>
