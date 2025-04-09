<template>
	<Drawer :closeOnEsc="false" v-model:visible="isMetricsOpen" position="right">
        Metrics
    </Drawer>
</template>
<script setup lang="ts">
import MetricsService from '@/services/metrics-service';
import Drawer from 'primevue/drawer';
import { watch } from 'vue';
import { Ref } from 'vue';

const isMetricsOpen = defineModel("isMetricsOpen", { default: false }) as Ref<boolean>;

function load() {
    MetricsService.get()
    .then((response) => {
        console.log(response);
    })
    .catch((error) => {
        console.error(error);
    });
}

watch(() => isMetricsOpen.value, (newValue) => {
    if (newValue) {
        load();
    }
});
</script>