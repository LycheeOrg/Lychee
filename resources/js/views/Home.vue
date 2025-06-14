<template>
	<!-- Nothing to see here is is more of a redirection component -->
</template>
<script setup lang="ts">
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useRouter } from "vue-router";

const router = useRouter();
const lycheeStore = useLycheeStateStore();
lycheeStore.init().then(() => {
	// Now that we loaded the stuff, we can redirect to the correct page
	if (lycheeStore.default_homepage === "gallery") {
		router.push({ name: "gallery" });
		// Insert other pages here later (feed or others...)
	} else {
		// Default
		router.push({ name: "gallery" });
	}
});
</script>
