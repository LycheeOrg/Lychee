declare module "*.vue";

// Only used by the embed build (see vite.embed.config.ts), which inlines .wasm
// files as base64 so the UMD bundle stays single-file and needs no import.meta.url.
declare module "*.wasm" {
	const base64: string;
	export default base64;
}
