<template>
	<div :dir>
		<slot />
	</div>
</template>
<script setup lang="ts">
import { useLtRorRtL } from "@/utils/Helpers";
const { dir } = useLtRorRtL();
</script>
