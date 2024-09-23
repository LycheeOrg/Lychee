<template>
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14">
			<template #start>
				<router-link :to="{ name: 'gallery' }">
					<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="" />
				</router-link>
			</template>

			<template #center>
				{{ $t("lychee.PROFILE") }}
			</template>

			<template #end> </template>
		</Toolbar>
		<SetLogin />
		<SetSecondFactor />
	</div>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import SetLogin from "@/components/forms/profile/SetLogin.vue";
import SetSecondFactor from "@/components/forms/profile/SetSecondFactor.vue";
</script>
