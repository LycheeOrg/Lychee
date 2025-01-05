<template>
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14">
			<template #start>
				<OpenLeftMenu />
			</template>

			<template #center>
				{{ $t("profile.title") }}
			</template>

			<template #end> </template>
		</Toolbar>
		<SetLogin />
		<SetOauth />
		<SetSecondFactor />
	</div>
</template>
<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import SetLogin from "@/components/forms/profile/SetLogin.vue";
import SetSecondFactor from "@/components/forms/profile/SetSecondFactor.vue";
import SetOauth from "@/components/forms/profile/SetOauth.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
</script>
