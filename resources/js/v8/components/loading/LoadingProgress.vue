<template>
	<UProgress v-if="isLoadingDesktop" class="rounded-none absolute w-full" />
	<Spinner v-if="isLoadingMobile" class="left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 absolute z-10 text-4xl" />
</template>
<script setup lang="ts">
import { computed, type Ref } from "vue";
import { isTouchDevice } from "@/utils/keybindings-utils";
import Spinner from "@/v8/components/Spinner.vue";

const loading = defineModel("loading") as Ref<boolean>;

const isLoadingMobile = computed(() => isTouchDevice() && loading.value);
const isLoadingDesktop = computed(() => !isTouchDevice() && loading.value);
</script>
