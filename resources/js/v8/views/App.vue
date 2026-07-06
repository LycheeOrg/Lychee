<template>
	<UApp>
		<UTheme :props="{ modal: { close: false } }">
			<ConfirmModalHost />
			<Error />
			<LeftMenu />
			<EmbedCodeDialog />
			<main>
				<router-view />
			</main>
		</UTheme>
	</UApp>
</template>

<script setup lang="ts">
import ConfirmModalHost from "@/v8/components/modals/ConfirmModalHost.vue";
import LeftMenu from "@/v8/menus/LeftMenu.vue";
import Error from "@/v8/views/Error.vue";
import EmbedCodeDialog from "@/v8/components/forms/album/EmbedCodeDialog.vue";
import { disableCtrlA } from "@/utils/keybindings-utils";

disableCtrlA();
</script>
