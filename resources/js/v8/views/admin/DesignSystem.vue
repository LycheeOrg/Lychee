<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("design-system.title") }}
	</UHeader>
	<UMain class="py-10">
		<UContainer :ui="{ base: 'max-w-5xl flex flex-col gap-10' }">
			<p class="text-muted">{{ $t("design-system.description") }}</p>

			<!-- Foundations -->
			<Fieldset :legend="$t('design-system.sections.foundations')">
				<dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 pt-2">
					<div v-for="row in foundationRows" :key="row.label">
						<dt class="text-xs uppercase tracking-wide text-muted font-semibold">{{ row.label }}</dt>
						<dd class="mt-0.5">{{ row.value }}</dd>
					</div>
				</dl>
			</Fieldset>

			<!-- Color palette -->
			<Fieldset :legend="$t('design-system.sections.colors')">
				<div class="flex flex-col gap-8 pt-2">
					<div v-for="group in colorGroups" :key="group.key">
						<p class="font-semibold mb-1">{{ $t(`design-system.colors.${group.key}`) }}</p>
						<p class="text-xs text-muted mb-3 font-mono">
							{{ $t("design-system.colors.resolved_from", { token: `--ui-color-${group.key}-*` }) }}
						</p>
						<div class="flex flex-wrap gap-2">
							<div v-for="shade in shades" :key="shade" class="flex flex-col items-center gap-1 w-16">
								<div
									:ref="(el) => registerSwatch(group.key, shade, el)"
									class="w-14 h-10 rounded-md border border-default"
									:style="{ backgroundColor: `var(--ui-color-${group.key}-${shade})` }"
								></div>
								<span class="text-2xs text-muted font-mono">{{ shade }}</span>
								<span class="text-3xs text-muted font-mono">{{ resolved[`${group.key}-${shade}`] ?? "…" }}</span>
							</div>
						</div>
					</div>
				</div>
			</Fieldset>

			<!-- Typography -->
			<Fieldset :legend="$t('design-system.sections.typography')">
				<div class="flex flex-col gap-6 pt-2">
					<div>
						<p class="text-3xl font-bold tracking-tight">{{ $t("design-system.typography.display") }}</p>
						<p class="text-2xs text-muted font-mono mt-1">text-3xl · font-bold</p>
					</div>
					<div>
						<p class="text-xl font-semibold">{{ $t("design-system.typography.heading") }}</p>
						<p class="text-2xs text-muted font-mono mt-1">text-xl · font-semibold</p>
					</div>
					<div>
						<p class="font-semibold text-sm text-muted uppercase tracking-wide mb-1">{{ $t("design-system.typography.body") }}</p>
						<p class="max-w-[65ch]">{{ $t("design-system.typography.body_sample") }}</p>
					</div>
					<div>
						<p class="font-semibold text-sm text-muted uppercase tracking-wide mb-1">{{ $t("design-system.typography.mono") }}</p>
						<p class="font-mono text-sm">{{ $t("design-system.typography.mono_sample") }}</p>
					</div>
					<div>
						<p class="font-semibold text-sm text-muted uppercase tracking-wide mb-2">{{ $t("design-system.typography.emphasis") }}</p>
						<div class="flex flex-col gap-1.5">
							<div v-for="tone in textTones" :key="tone.class" class="flex items-center gap-3">
								<code class="text-2xs font-mono text-muted w-28 shrink-0">{{ tone.class }}</code>
								<p :class="tone.class">{{ $t("design-system.typography.emphasis_sample") }}</p>
							</div>
						</div>
					</div>
				</div>
			</Fieldset>

			<!-- Buttons -->
			<Fieldset :legend="$t('design-system.sections.buttons')">
				<p class="text-sm text-muted mb-4 mt-2 max-w-[65ch]">{{ $t("design-system.buttons.intro") }}</p>
				<div class="overflow-x-auto">
					<table class="text-sm">
						<thead>
							<tr>
								<th class="text-left pr-4 pb-2 text-xs uppercase tracking-wide text-muted font-semibold">
									{{ $t("design-system.sections.buttons") }}
								</th>
								<th
									v-for="variant in buttonVariants"
									:key="variant"
									class="text-left pr-4 pb-2 text-xs uppercase tracking-wide text-muted font-semibold font-mono"
								>
									{{ variant }}
								</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="color in buttonColors" :key="color">
								<td class="pr-4 py-1.5 font-mono text-xs text-muted">{{ color }}</td>
								<td v-for="variant in buttonVariants" :key="variant" class="pr-4 py-1.5">
									<UButton :color="color" :variant="variant" size="sm">{{ color }}</UButton>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</Fieldset>

			<!-- Badges & chips -->
			<Fieldset :legend="$t('design-system.sections.badges')">
				<div class="flex flex-wrap gap-2 pt-2">
					<UBadge v-for="color in buttonColors" :key="color" :color="color" variant="soft">{{ color }}</UBadge>
				</div>
				<div class="flex flex-wrap gap-2 mt-3">
					<UBadge v-for="color in buttonColors" :key="`${color}-solid`" :color="color">{{ color }}</UBadge>
				</div>
			</Fieldset>

			<!-- Form controls -->
			<Fieldset :legend="$t('design-system.sections.forms')">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 max-w-3xl">
					<UFormField :label="$t('design-system.forms.input_label')">
						<UInput v-model="demo.title" :placeholder="$t('design-system.forms.input_placeholder')" class="w-full" />
					</UFormField>
					<UFormField :label="$t('design-system.forms.select_label')">
						<USelectMenu v-model="demo.sort" :items="sortOptions" label-key="label" class="w-full" />
					</UFormField>
					<UFormField>
						<UCheckbox v-model="demo.sensitive" :label="$t('design-system.forms.checkbox_label')" />
					</UFormField>
					<UFormField>
						<USwitch
							v-model="demo.isPublic"
							:label="$t('design-system.forms.switch_label')"
							:description="$t('design-system.forms.switch_description')"
						/>
					</UFormField>
					<UFormField :label="$t('design-system.forms.textarea_label')" class="md:col-span-2">
						<UTextarea v-model="demo.description" :placeholder="$t('design-system.forms.textarea_placeholder')" class="w-full" />
					</UFormField>
					<UFormField :label="$t('design-system.forms.radio_legend')" class="md:col-span-2">
						<URadioGroup v-model="demo.tagLogic" orientation="horizontal" :items="tagLogicItems" />
					</UFormField>
				</div>
			</Fieldset>

			<!-- Feedback & states -->
			<Fieldset :legend="$t('design-system.sections.feedback')">
				<div class="flex flex-col gap-3 pt-2 max-w-2xl">
					<UAlert color="info" variant="soft" :description="$t('design-system.feedback.alert_info')" />
					<UAlert color="success" variant="soft" :description="$t('design-system.feedback.alert_success')" />
					<UAlert color="warning" variant="soft" :description="$t('design-system.feedback.alert_warning')" />
					<UAlert color="error" variant="soft" :description="$t('design-system.feedback.alert_error')" />
				</div>
				<div class="mt-6 max-w-2xl">
					<p class="text-xs uppercase tracking-wide text-muted font-semibold mb-2">UProgress</p>
					<UProgress :model-value="62" />
				</div>
				<div class="mt-6 max-w-2xl">
					<UEmpty
						icon="lucide:share-2"
						:title="$t('design-system.feedback.empty_title')"
						:description="$t('design-system.feedback.empty_description')"
					/>
				</div>
				<div class="flex flex-wrap gap-8 mt-6">
					<div class="flex flex-col items-center gap-2">
						<LycheeLoadingIcon class="w-16 h-16" />
						<span class="text-2xs text-muted font-mono">{{ $t("design-system.loading.full_page") }}</span>
					</div>
					<div class="flex flex-col items-center gap-2">
						<LycheeLoadingIcon fast class="text-xl" />
						<span class="text-2xs text-muted font-mono">{{ $t("design-system.loading.mini_spinner") }}</span>
					</div>
				</div>
				<p class="text-3xs text-muted mt-3 max-w-2xl">
					{{ $t("design-system.loading.full_page_description") }}
					{{ $t("design-system.loading.mini_spinner_description") }}
				</p>
			</Fieldset>

			<!-- Radius & elevation -->
			<Fieldset :legend="$t('design-system.sections.radius')">
				<div class="flex flex-wrap gap-6 pt-2">
					<div v-for="r in radiusSamples" :key="r.label" class="flex flex-col items-center gap-2">
						<div class="w-16 h-16 bg-elevated border border-default" :class="r.class"></div>
						<span class="text-2xs text-muted font-mono">{{ r.label }}</span>
					</div>
				</div>
				<div class="flex flex-wrap gap-8 pt-8 mt-6 border-t border-default">
					<div v-for="s in shadowSamples" :key="s.label" class="flex flex-col items-center gap-3">
						<div class="w-16 h-16 rounded-lg bg-elevated" :class="s.class"></div>
						<span class="text-2xs text-muted font-mono">{{ s.label }}</span>
					</div>
				</div>
			</Fieldset>
		</UContainer>
	</UMain>
</template>
<script setup lang="ts">
import { reactive, ref, type ComponentPublicInstance } from "vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import { trans } from "laravel-vue-i18n";

const shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950] as const;

const colorGroups = [
	{ key: "primary" },
	{ key: "secondary" },
	{ key: "neutral" },
	{ key: "success" },
	{ key: "warning" },
	{ key: "error" },
	{ key: "info" },
] as const;

const buttonColors = ["primary", "secondary", "neutral", "success", "warning", "error"] as const;
const buttonVariants = ["solid", "outline", "soft", "ghost"] as const;

// Ordered from most to least emphasis (see --ui-text-* in app-v8.css / @nuxt/ui).
// text-dimmed exists in the token set but isn't used anywhere in the v8 tree today.
const textTones = [
	{ class: "text-highlighted" },
	{ class: "text-default" },
	{ class: "text-toned" },
	{ class: "text-muted" },
	{ class: "text-dimmed" },
];

const radiusSamples = [
	{ label: "rounded-md", class: "rounded-md" },
	{ label: "rounded-lg", class: "rounded-lg" },
	{ label: "rounded-xl", class: "rounded-xl" },
	{ label: "rounded-full", class: "rounded-full" },
];

const shadowSamples = [
	{ label: "shadow-inner", class: "shadow-black shadow-inner" },
	{ label: "shadow-sm", class: "shadow-black shadow-sm" },
	{ label: "shadow-md", class: "shadow-black shadow-md" },
	{ label: "shadow-lg", class: "shadow-black shadow-lg" },
	{ label: "shadow-xl", class: "shadow-black shadow-xl" },
	{ label: "shadow-md shadow-black/25", class: "shadow-md shadow-black/25" },
];

const foundationRows = [
	{ label: trans("design-system.foundations.system"), value: trans("design-system.foundations.system_value") },
	{ label: trans("design-system.foundations.body_font"), value: trans("design-system.foundations.body_font_value") },
	{ label: trans("design-system.foundations.mono_font"), value: trans("design-system.foundations.mono_font_value") },
	{ label: trans("design-system.foundations.icons"), value: trans("design-system.foundations.icons_value") },
	{ label: trans("design-system.foundations.theme"), value: trans("design-system.foundations.theme_value") },
];

const sortOptions = [
	{ label: trans("design-system.forms.select_label"), value: "" },
	{ label: "Take date", value: "taken_at" },
	{ label: "Upload date", value: "created_at" },
	{ label: "Title", value: "title" },
];
const tagLogicItems = ["AND", "OR"];

const demo = reactive({
	title: "",
	sort: sortOptions[0],
	sensitive: false,
	isPublic: true,
	description: "",
	tagLogic: "AND",
});

// Colors are consumed as CSS custom properties (see resources/sass/app-v8.css),
// so the swatches above are always visually correct even without JS. This just
// resolves each swatch's *actual* computed color so the page can show real hex
// values instead of hand-copied ones that would drift the moment a token changes.
const resolved = ref<Record<string, string>>({});

function registerSwatch(colorKey: string, shade: number, el: Element | ComponentPublicInstance | null) {
	if (!(el instanceof HTMLElement)) {
		return;
	}
	const rgb = getComputedStyle(el).backgroundColor;
	resolved.value[`${colorKey}-${shade}`] = rgbToHex(rgb);
}

function rgbToHex(rgb: string): string {
	const match = rgb.match(/\d+(\.\d+)?/g);
	if (!match || match.length < 3) {
		return rgb;
	}
	const [r, g, b] = match.map((n) => Math.round(parseFloat(n)));
	return (
		"#" +
		[r, g, b]
			.map((n) => n.toString(16).padStart(2, "0"))
			.join("")
			.toUpperCase()
	);
}
</script>
