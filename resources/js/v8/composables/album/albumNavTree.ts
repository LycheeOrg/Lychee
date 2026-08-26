import { computed, reactive, watch, type ComputedRef } from "vue";
import type { AlbumListRow, AlbumTreeNode } from "@/stores/AlbumListState";

export type AlbumNavRow = {
	node: AlbumTreeNode;
	depth: number;
	hasChildren: boolean;
};

// Module-level singleton, not per-call state: it must survive `AlbumNavTree.vue` unmounting (the
// mobile slideover's content only exists while open) and must NOT reset when the store's `tree`
// changes after `invalidate()` - which fires routinely after ordinary album actions (move, merge,
// rename, delete, login), not just structural edits. Resetting on every one of those would collapse
// the whole tree back to root far more often than a user would expect.
const expandedIds = reactive(new Set<string>());

function isExpanded(id: string): boolean {
	return expandedIds.has(id);
}

function toggle(id: string): void {
	if (expandedIds.has(id)) {
		expandedIds.delete(id);
	} else {
		expandedIds.add(id);
	}
}

function expandAllNodes(nodes: AlbumTreeNode[]): void {
	for (const node of nodes) {
		if (node.children.length > 0) {
			expandedIds.add(node.id);
			expandAllNodes(node.children);
		}
	}
}

function collapseAll(): void {
	expandedIds.clear();
}

function findAncestorIds(rows: AlbumListRow[], target: AlbumListRow): string[] {
	return rows.filter((r) => r.id !== target.id && r._lft < target._lft && r._rgt > target._rgt).map((r) => r.id);
}

/**
 * Flattens the store's nested `tree` into the rows a virtualized list can render directly: only
 * nodes whose ancestors are all expanded are included, in depth-first order. Root nodes are always
 * included regardless of their own expand state - only descending past them is gated.
 */
export function useAlbumNavFlatTree(
	tree: ComputedRef<AlbumTreeNode[]>,
	activeAlbumId: ComputedRef<string | undefined>,
	rows: ComputedRef<AlbumListRow[]>,
) {
	// Ensures the active album's ancestor chain is expanded - additive only, never collapses
	// branches the user closed elsewhere.
	watch(
		activeAlbumId,
		(id) => {
			if (id === undefined) {
				return;
			}
			const target = rows.value.find((r) => r.id === id);
			if (target === undefined) {
				return;
			}
			for (const ancestorId of findAncestorIds(rows.value, target)) {
				expandedIds.add(ancestorId);
			}
		},
		{ immediate: true },
	);

	const flatRows = computed<AlbumNavRow[]>(() => {
		const result: AlbumNavRow[] = [];

		function visit(nodes: AlbumTreeNode[], depth: number): void {
			for (const node of nodes) {
				const hasChildren = node.children.length > 0;
				result.push({ node, depth, hasChildren });
				if (hasChildren && isExpanded(node.id)) {
					visit(node.children, depth + 1);
				}
			}
		}

		visit(tree.value, 0);
		return result;
	});

	function expandAll(): void {
		expandAllNodes(tree.value);
	}

	return { flatRows, isExpanded, toggle, expandAll, collapseAll };
}
