import AlbumService from "@/services/album-service";
import { useRandomId } from "@/composables/useRandomId";
import { type Ref } from "vue";
import { type Uploadable } from "@/composables/album/uploadEvents";

const generateId = useRandomId();

export function getEntry(item: DataTransferItem): FileSystemEntry | null {
	return item.webkitGetAsEntry?.() ?? (item as DataTransferItem & { getAsEntry?: () => FileSystemEntry | null }).getAsEntry?.() ?? null;
}

export function hasDirectoryEntry(items: DataTransferItemList): boolean {
	for (let i = 0; i < items.length; i++) {
		const entry = getEntry(items[i]);
		if (entry?.isDirectory) {
			return true;
		}
	}
	return false;
}

function readDirectoryEntries(dirEntry: FileSystemDirectoryEntry): Promise<FileSystemEntry[]> {
	const reader = dirEntry.createReader();
	const all: FileSystemEntry[] = [];

	function readBatch(): Promise<FileSystemEntry[]> {
		return new Promise((resolve, reject) => {
			reader.readEntries((entries) => {
				if (entries.length === 0) {
					resolve(all);
					return;
				}
				all.push(...entries);
				readBatch().then(resolve).catch(reject);
			}, reject);
		});
	}

	return readBatch();
}

function fileEntryToFile(fileEntry: FileSystemFileEntry): Promise<File> {
	return new Promise((resolve, reject) => fileEntry.file(resolve, reject));
}

function resolveOrCreateAlbum(
	name: string,
	parent_id: string | null,
	existingAlbums: { id: string; title: string }[],
): Promise<{ id: string; existed: boolean }> {
	const match = existingAlbums.find((a) => a.title.toLowerCase() === name.toLowerCase());
	if (match !== undefined) {
		return Promise.resolve({ id: match.id, existed: true });
	}
	return AlbumService.createAlbum({ title: name, parent_id }).then((response) => ({ id: response.data, existed: false }));
}

async function fetchChildAlbums(albumId: string): Promise<{ id: string; title: string }[]> {
	const result: { id: string; title: string }[] = [];
	let page = 1;
	let lastPage: number;
	do {
		const response = await AlbumService.getAlbums(albumId, page);
		result.push(...response.data.data.map((a) => ({ id: a.id, title: a.title })));
		lastPage = response.data.last_page;
		page++;
	} while (page <= lastPage);
	return result;
}

function processDirectory(
	dirEntry: FileSystemDirectoryEntry,
	parent_id: string | null,
	existingAlbums: { id: string; title: string }[],
	list_upload_files: Ref<Uploadable[]>,
	currentDepth: number,
	maxDepth: number,
	onError: (message: string) => void,
): Promise<boolean> {
	if (maxDepth > 0 && currentDepth > maxDepth) {
		return Promise.resolve(false);
	}

	return resolveOrCreateAlbum(dirEntry.name, parent_id, existingAlbums)
		.then(({ id: albumId, existed }) => {
			const childAlbumsPromise = existed ? fetchChildAlbums(albumId) : Promise.resolve([]);
			return Promise.all([readDirectoryEntries(dirEntry), childAlbumsPromise]).then(([entries, childAlbums]) => {
				const fileEntries = entries.filter((e) => e.isFile) as FileSystemFileEntry[];
				const subDirEntries = entries.filter((e) => e.isDirectory) as FileSystemDirectoryEntry[];

				const filePromises = fileEntries.map((fe) =>
					fileEntryToFile(fe).then((file) => {
						list_upload_files.value.push({
							uid: generateId(),
							file,
							album_id: albumId,
							albumTitle: dirEntry.name,
							status: "waiting",
						});
					}),
				);

				const subDirPromises = subDirEntries.map((sub) =>
					processDirectory(sub, albumId, childAlbums, list_upload_files, currentDepth + 1, maxDepth, onError),
				);

				return Promise.allSettled([...filePromises, ...subDirPromises]).then((results) => {
					results.forEach((r) => {
						if (r.status === "rejected") {
							onError(String(r.reason));
						}
					});
					return fileEntries.length > 0 || subDirEntries.length > 0;
				});
			});
		})
		.catch((err: unknown) => {
			onError(`Failed to create album "${dirEntry.name}": ${String(err)}`);
			return false;
		});
}

export function handleFolderDrop(
	items: DataTransferItemList,
	parent_id: string | null,
	existingAlbums: { id: string; title: string }[],
	list_upload_files: Ref<Uploadable[]>,
	maxDepth: number,
	onError: (message: string) => void,
): Promise<boolean> {
	const dirEntries: FileSystemDirectoryEntry[] = [];
	const fileEntries: FileSystemFileEntry[] = [];

	for (let i = 0; i < items.length; i++) {
		const entry = getEntry(items[i]);
		if (entry === null) continue;
		if (entry.isDirectory) {
			dirEntries.push(entry as FileSystemDirectoryEntry);
		} else if (entry.isFile) {
			fileEntries.push(entry as FileSystemFileEntry);
		}
	}

	// Flat files go directly to the queue without an album_id override.
	const flatFilePromises = fileEntries.map((fe) =>
		fileEntryToFile(fe).then((file) => {
			list_upload_files.value.push({ uid: generateId(), file, status: "waiting" });
		}),
	);

	const dirPromises = dirEntries.map((dir) => processDirectory(dir, parent_id, existingAlbums, list_upload_files, 1, maxDepth, onError));

	return Promise.allSettled([...flatFilePromises, ...dirPromises]).then((results) => {
		results.forEach((r) => {
			if (r.status === "rejected") {
				onError(String(r.reason));
			}
		});
		return list_upload_files.value.length > 0;
	});
}
