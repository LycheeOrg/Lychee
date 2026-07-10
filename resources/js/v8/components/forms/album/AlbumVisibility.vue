<template>
	<UCard class="text-sm p-4 xl:px-9 w-full xl:basis-1/3 xl:min-w-0" :ui="{ body: 'p-0' }">
		<form>
			<USwitch
				v-model="is_public"
				:label="$t('dialogs.visibility.public')"
				:description="$t('dialogs.visibility.public_expl')"
				:ui="{ label: `font-bold` }"
				@change="save"
			/>
			<Collapse v-if="albumStore.config?.is_base_album" :when="is_public">
				<USwitch
					v-model="grants_full_photo_access"
					class="my-4"
					:label="$t('dialogs.visibility.full')"
					:description="$t('dialogs.visibility.full_expl')"
					:ui="{ label: `font-bold` }"
					@change="save"
				/>
				<USwitch
					v-model="is_link_required"
					class="my-4"
					:label="$t('dialogs.visibility.hidden')"
					:description="$t('dialogs.visibility.hidden_expl')"
					:ui="{ label: `font-bold` }"
					@change="save"
				/>
				<USwitch
					v-model="grants_download"
					class="my-4"
					:label="$t('dialogs.visibility.downloadable')"
					:description="$t('dialogs.visibility.downloadable_expl')"
					:ui="{ label: `font-bold` }"
					@change="save"
				/>
				<USwitch
					v-if="is_se_enabled || is_se_preview_enabled"
					v-model="grants_upload"
					:disabled="!is_se_enabled"
					class="my-4"
					color="error"
					:ui="{ label: grants_upload ? 'font-bold text-red-700' : 'font-bold' }"
					@change="save"
				>
					<template #label>{{ $t("dialogs.visibility.upload") }} <SETag v-if="is_se_preview_enabled" class="ml-2" /></template>
					<template #description><span v-html="$t('dialogs.visibility.upload_expl')"></span></template>
				</USwitch>
				<USwitch
					v-if="!can_pasword_protect && is_password_required"
					v-model="is_password_required"
					class="my-4"
					color="error"
					:ui="{ label: 'font-bold inline-flex items-center gap-2', description: 'text-error/80' }"
					@change="save"
				>
					<template #label>
						<span class="border-b border-dashed border-error">{{ $t("dialogs.visibility.password_prot") }}</span>
						<UIcon name="prime:exclamation-triangle" class="text-error" />
					</template>
					<template #description><span v-html="$t('dialogs.visibility.password_prop_not_compatible')"></span></template>
				</USwitch>
				<template v-else-if="can_pasword_protect">
					<USwitch
						v-model="is_password_required"
						class="my-4"
						:label="$t('dialogs.visibility.password_prot')"
						:description="$t('dialogs.visibility.password_prot_expl')"
						:ui="{ label: `font-bold ${publicStateTextClass}`, description: publicStateTextClass }"
						@change="save"
					/>
					<UFormField v-if="is_password_required" :label="$t('dialogs.visibility.password')">
						<InputPassword id="password" v-model="password" autocomplete="new-password" @change="save" />
					</UFormField>
				</template>
			</Collapse>
			<template v-if="albumStore.config?.is_base_album">
				<hr class="block mt-8 mb-8 w-full border-t border-solid border-default" />
				<USwitch
					v-model="is_nsfw"
					class="my-4"
					color="error"
					:label="$t('dialogs.visibility.nsfw')"
					:description="$t('dialogs.visibility.nsfw_expl')"
					:ui="{ label: is_nsfw ? 'font-bold text-red-700' : 'font-bold' }"
					@change="save"
				/>
			</template>
		</form>
	</UCard>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import AlbumService, { UpdateProtectionPolicyData } from "@/services/album-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { Collapse } from "vue-collapsed";
import { trans } from "laravel-vue-i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import SETag from "@/v8/components/icons/SETag.vue";
import { useAlbumStore } from "@/stores/AlbumState";

const albumStore = useAlbumStore();

const toast = useAppToast();

const is_public = ref<boolean>(albumStore.album?.policy.is_public ?? false);
const is_link_required = ref<boolean>(albumStore.album?.policy.is_link_required ?? false);
const is_nsfw = ref<boolean>(albumStore.album?.policy.is_nsfw ?? false);
const grants_full_photo_access = ref<boolean>(albumStore.album?.policy.grants_full_photo_access ?? false);
const grants_download = ref<boolean>(albumStore.album?.policy.grants_download ?? false);
const is_password_required = ref<boolean>(albumStore.album?.policy.is_password_required ?? false);
const password = ref<string>("");
const can_pasword_protect = ref<boolean>(albumStore.album?.rights.can_pasword_protect ?? false);
const grants_upload = ref<boolean>(albumStore.album?.policy.grants_upload ?? false);

const lycheeStore = useLycheeStateStore();
const { is_se_enabled, is_se_preview_enabled } = storeToRefs(lycheeStore);

const publicStateTextClass = computed(() => (is_public.value ? "text-highlighted" : "text-muted"));

function save() {
	const data: UpdateProtectionPolicyData = {
		album_id: <string>albumStore.albumId,
		is_public: is_public.value,
		is_link_required: is_link_required.value,
		is_nsfw: is_nsfw.value,
		grants_full_photo_access: grants_full_photo_access.value,
		grants_download: grants_download.value,
		grants_upload: grants_upload.value,
		password: is_password_required.value ? password.value : undefined,
	};

	AlbumService.updateProtectionPolicy(data).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("dialogs.visibility.visibility_updated"), life: 3000 });
		AlbumService.clearCache(albumStore.albumId);
		if (albumStore.config?.is_model_album) {
			AlbumService.clearCache(albumStore.modelAlbum?.parent_id);
		} else {
			AlbumService.clearAlbums();
		}
	});
}
</script>
