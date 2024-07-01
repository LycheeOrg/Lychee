<template>
	<div class="w-full">
		<Toolbar>
			<template #start>
				<Button icon="pi pi-plus" class="mr-2" severity="secondary" text />
				<Button icon="pi pi-print" class="mr-2" severity="secondary" text />
				<Button icon="pi pi-upload" severity="secondary" text />
			</template>

			<template #center>
				<IconField>
					<InputIcon>
						<i class="pi pi-search" />
					</InputIcon>
					<InputText placeholder="Search" />
				</IconField>
			</template>

			<template #end>
				<!-- <SplitButton label="Save" :model="items"></SplitButton> -->
			</template>
		</Toolbar>
		<!-- <HeaderBar>
			<template v-if="!isLoggedIn">
				<HeaderButton icon="account-login" :class="isLoginLeft ? 'order-0' : 'order-4'" @click="" />
			</template>
			<template v-else>
				<HeaderButton icon="cog" @click="openLeftMenu" />
			</template>
			<HeaderTitle>{{ title }}</HeaderTitle>
		</HeaderBar> -->
	</div>
</template>
<script setup lang="ts">
import HeaderBar from "@/components/headers/HeaderBar.vue";
import HeaderButton from "@/components/headers/HeaderButton.vue";
import HeaderTitle from "@/components/headers/HeaderTitle.vue";
import Toolbar from "primevue/toolbar";
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
