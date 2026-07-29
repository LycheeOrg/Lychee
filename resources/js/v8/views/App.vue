<template>
	<UApp>
		<UTheme :props="theme.props" :ui="theme.ui">
			<ConfirmModalHost />
			<Error />
			<LeftMenu />
			<EmbedCodeDialog />
			<main class="relative">
				<router-view v-slot="{ Component, route }">
					<Transition name="lychee-page">
						<component :is="Component" :key="route.name ?? route.path" />
					</Transition>
				</router-view>
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
import { theme } from "@/v8/style/theme";

disableCtrlA();
</script>

<style>
/*
  Route-change transition for the routed page itself, independent of any
  per-page LoadingProgress overlay: pages that load fast (or don't show a
  LoadingProgress at all) would otherwise hard-cut between two unrelated DOM
  trees.

  No `mode="out-in"` here on purpose: with it, the outgoing page (and its
  LoadingProgress, which lives inside that page) fully unmounts before the
  incoming page mounts, leaving a gap where `<main>` has no child at all -
  visually a hard cut to whatever's behind it (black in dark mode) with no
  loading indicator in the DOM to cover it. Leaving enter/leave to run
  concurrently (default) means the incoming page - and its own
  immediately-true LoadingProgress/spinner - mounts right away, covering the
  outgoing page's fade with its own overlay instead of exposing a gap.
  `.lychee-page-leave-active` is taken out of flow via `position: absolute`
  so the incoming page can occupy normal flow immediately rather than being
  pushed down by the still-fading-out old one.
*/
.lychee-page-enter-active,
.lychee-page-leave-active {
	transition:
		opacity 0.18s ease,
		transform 0.18s ease;
}
.lychee-page-leave-active {
	position: absolute;
	inset: 0;
	width: 100%;
}
.lychee-page-enter-from,
.lychee-page-leave-to {
	opacity: 0;
	transform: translateY(8px);
}
</style>
