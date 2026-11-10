<template>
	<VChart :option="props.option" :init-options="{ renderer: 'svg' }" :style="{ height: `${props.height}px` }" class="w-full" autoresize />
</template>
<script setup lang="ts">
import type { EChartsOption } from "echarts";
import VChart from "vue-echarts";
import "@/v8/utils/insights/echarts";

const props = defineProps<{ option: EChartsOption; height: number }>();
</script>
