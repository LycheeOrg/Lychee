<template>
	<div class="w-full">
		<HeaderBar>
			<template v-if="!isLoggedIn">
				<HeaderButton icon="account-login" :class="isLoginLeft ? 'order-0' : 'order-4'" @click="" />
			</template>
			<template v-else>
				<HeaderButton icon="cog" @click="openLeftMenu" />
			</template>
			<HeaderTitle>{{ title }}</HeaderTitle>
			<!-- <HeaderButton /> -->
		</HeaderBar>
	</div>
</template>
<script setup lang="ts">
import HeaderBar from "@/components/headers/HeaderBar.vue";
import HeaderButton from "@/components/headers/HeaderButton.vue";
import HeaderTitle from "@/components/headers/HeaderTitle.vue";
import { ref } from "vue";

const isLoggedIn = ref(false);
const isLoginLeft = ref(false);
const isSearchAccessible = ref(false);
const title = ref("Albums");

const emit = defineEmits(["toggleLeftMenu"]);

function openLeftMenu() {
	emit("toggleLeftMenu");
}
</script>
