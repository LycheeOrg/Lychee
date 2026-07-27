import { onMounted, onUnmounted, ref } from "vue";
import { currentLocale, trans } from "laravel-vue-i18n";

const WELCOME_KEY = "profile.admin_setup.welcome";
const ROTATE_INTERVAL_MS = 3000;
const RTL_LOCALES = new Set(["ar", "fa"]);

const langLoaders = import.meta.glob<{ default: Record<string, string> }>("../../../../lang/php_*.json");

type Direction = "ltr" | "rtl";

interface WelcomeEntry {
	text: string;
	dir: Direction;
}

function localeFromPath(path: string): string {
	return path.replace(/^.*php_/, "").replace(/\.json$/, "");
}

function dirForLocale(locale: string): Direction {
	return RTL_LOCALES.has(locale) ? "rtl" : "ltr";
}

export function useAdminWelcomeRotator() {
	const text = ref(trans(WELCOME_KEY));
	const dir = ref<Direction>(dirForLocale(currentLocale.value));

	let entries: WelcomeEntry[] = [];
	let index = 0;
	let timer: ReturnType<typeof setInterval> | undefined;

	function advance() {
		if (entries.length === 0) return;
		index = (index + 1) % entries.length;
		text.value = entries[index].text;
		dir.value = entries[index].dir;
	}

	async function preload() {
		const loaded = await Promise.all(
			Object.entries(langLoaders).map(async ([path, load]) => {
				const mod = await load();
				const message = mod.default[WELCOME_KEY];
				return message ? { text: message, dir: dirForLocale(localeFromPath(path)) } : undefined;
			}),
		);
		entries = loaded.filter((entry): entry is WelcomeEntry => !!entry);
	}

	onMounted(async () => {
		await preload();
		timer = setInterval(advance, ROTATE_INTERVAL_MS);
	});

	onUnmounted(() => {
		if (timer) clearInterval(timer);
	});

	return { text, dir };
}
