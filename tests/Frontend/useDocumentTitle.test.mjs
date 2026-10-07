import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { test } from "node:test";
import { transpileModule, ModuleKind } from "typescript";
import { effectScope, nextTick, reactive, watchEffect } from "vue";

const source = readFileSync(new URL("../../resources/js/composables/useDocumentTitle.ts", import.meta.url), "utf8");
const { outputText } = transpileModule(source, { compilerOptions: { module: ModuleKind.CommonJS } });

// Exercise the real watcher with Vue reactivity, without loading the stores'
// unrelated API and browser dependencies. No test framework is added.
function mountTitle(t, name = "album") {
	const albumStore = reactive({ album: { id: "holiday", title: "Summer holiday" } });
	const photoStore = reactive({ photo: undefined });
	const siteStore = reactive({ title: "Example Gallery" });
	const route = reactive({ name, params: { albumId: "holiday" } });
	const document = { title: "" };
	const modules = {
		vue: { watchEffect },
		"vue-router": { useRoute: () => route },
		"@/stores/AlbumState": { useAlbumStore: () => albumStore },
		"@/stores/PhotoState": { usePhotoStore: () => photoStore },
		"@/stores/LycheeState": { useLycheeStateStore: () => siteStore },
	};
	const exports = {};
	new Function("require", "exports", "document", outputText)((name) => modules[name], exports, document);
	const scope = effectScope();
	scope.run(() => exports.useDocumentTitle());
	t.after(() => scope.stop());
	return { albumStore, photoStore, siteStore, route, document };
}

test("album tabs follow loaded titles and site title changes", async (t) => {
	const { albumStore, siteStore, document } = mountTitle(t);
	assert.equal(document.title, "Summer holiday · Example Gallery");
	albumStore.album.title = "Winter holiday";
	siteStore.title = "Sample Gallery";
	await nextTick();
	assert.equal(document.title, "Winter holiday · Sample Gallery");
});

test("closing a photo or video restores the album title", async (t) => {
	const { photoStore, document } = mountTitle(t);
	for (const title of ["Beach sunset", "Holiday video"]) {
		photoStore.photo = { title };
		await nextTick();
		assert.equal(document.title, title);
		photoStore.photo = undefined;
		await nextTick();
		assert.equal(document.title, "Summer holiday · Example Gallery");
	}
});

test("leaving an album ignores retained album metadata", async (t) => {
	const { route, document } = mountTitle(t);
	for (const name of ["gallery", "search", "map", "timeline", "settings"]) {
		route.name = name;
		await nextTick();
		assert.equal(document.title, "Example Gallery");
	}
});

test("navigation between albums uses the previous title until metadata loads", async (t) => {
	const { albumStore, route, document } = mountTitle(t);
	route.params.albumId = "winter";
	await nextTick();
	assert.equal(document.title, "Summer holiday · Example Gallery");
	albumStore.album = { id: "winter", title: "Winter holiday" };
	await nextTick();
	assert.equal(document.title, "Winter holiday · Example Gallery");
});

test("unavailable or empty titles fall back to literal site and album titles", async (t) => {
	const { albumStore, photoStore, siteStore, document } = mountTitle(t);
	albumStore.album = undefined;
	await nextTick();
	assert.equal(document.title, "Example Gallery");
	albumStore.album = { id: "holiday", title: "" };
	await nextTick();
	assert.equal(document.title, "Example Gallery");
	albumStore.album.title = "gallery.recent";
	photoStore.photo = { title: "" };
	await nextTick();
	assert.equal(document.title, "gallery.recent · Example Gallery");
	albumStore.album.title = "Recent";
	siteStore.title = "gallery.recent";
	await nextTick();
	assert.equal(document.title, "Recent · gallery.recent");
	albumStore.album = undefined;
	siteStore.title = "gallery.title";
	await nextTick();
	assert.equal(document.title, "gallery.title");
});

test("flow albums use the album title too", (t) => {
	const { document } = mountTitle(t, "flow-album");
	assert.equal(document.title, "Summer holiday · Example Gallery");
});

test("media outside an album keeps its existing title behaviour", async (t) => {
	const { photoStore, document } = mountTitle(t, "timeline");
	photoStore.photo = { title: "Beach sunset" };
	await nextTick();
	assert.equal(document.title, "Beach sunset");
	photoStore.photo = undefined;
	await nextTick();
	assert.equal(document.title, "Example Gallery");
});
