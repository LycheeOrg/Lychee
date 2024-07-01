<template></template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const path = computed(() => route.path);
</script>
