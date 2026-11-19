/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { EChartsOption } from "echarts";
import type { InsightsPalette } from "@/v8/composables/insights/useInsightsTheme";
import { escapeHtml } from "../format";
import { tooltipStyle } from "./common";

export type SpaceNode = { name: string; value: number; children?: SpaceNode[] };

type AlbumSpace = App.Http.Resources.Statistics.Album;

/**
 * Album tree from the nested-set bounds of the album-space rows (FR-085-20).
 * A node's value is its own size plus its descendants'. With `byOwner`, the
 * roots are grouped under their owner.
 */
export function spaceTree(albums: AlbumSpace[], byOwner: boolean): SpaceNode[] {
	const sorted = [...albums].sort((a, b) => a.left - b.left);
	const roots: SpaceNode[] = [];
	const owners = new Map<string, SpaceNode>();
	const stack: { album: AlbumSpace; node: SpaceNode }[] = [];

	for (const album of sorted) {
		while (stack.length > 0 && stack[stack.length - 1].album.right < album.left) {
			stack.pop();
		}
		const node: SpaceNode = { name: album.title, value: album.size, children: [] };
		const parent = stack[stack.length - 1];
		if (parent !== undefined && album.right < parent.album.right) {
			parent.node.children!.push(node);
		} else if (byOwner) {
			const owner = owners.get(album.username) ?? { name: album.username, value: 0, children: [] };
			owner.children!.push(node);
			owners.set(album.username, owner);
		} else {
			roots.push(node);
		}
		stack.push({ album, node });
	}

	const top = byOwner ? [...owners.values()] : roots;
	return prune(top);
}

/** Adds descendants to each node's value and drops nodes without any space. */
function prune(nodes: SpaceNode[]): SpaceNode[] {
	return nodes.filter((node) => {
		node.children = prune(node.children ?? []);
		node.value += node.children.reduce((sum, child) => sum + child.value, 0);
		if (node.children.length === 0) {
			delete node.children;
		}
		return node.value > 0;
	});
}

/** Colour of each depth, from full primary down to its faintest step (single accent hue, DESIGN.md). */
function depthLevels(palette: InsightsPalette) {
	const colours = [palette.steps[3], palette.steps[2], palette.steps[1], palette.steps[0]];
	return colours.map((color) => ({ itemStyle: { color, borderColor: palette.background } }));
}

/** Sunburst or treemap of the album tree (FR-085-20); `name` labels the root in the treemap breadcrumb. */
export function spaceOption(
	tree: SpaceNode[],
	kind: "sunburst" | "treemap",
	palette: InsightsPalette,
	size: (bytes: number) => string,
	name: string,
): EChartsOption {
	const tooltip = {
		...tooltipStyle(palette),
		formatter: (params: unknown) => {
			const item = params as { name: string; value: number; treePathInfo?: { name: string }[] };
			const path = (item.treePathInfo ?? []).map((step) => step.name).filter((step) => step !== "" && step !== name);
			return `${escapeHtml(path.length > 0 ? path.join(" / ") : item.name)}: <b>${escapeHtml(size(item.value))}</b>`;
		},
	};

	if (kind === "sunburst") {
		return {
			tooltip,
			series: [
				{
					type: "sunburst",
					data: tree,
					radius: ["10%", "95%"],
					sort: undefined,
					nodeClick: "rootToNode",
					itemStyle: { borderColor: palette.background, borderWidth: 2 },
					label: { color: palette.text, fontSize: 10, minAngle: 12 },
					levels: [{}, ...depthLevels(palette)],
					emphasis: { focus: "ancestor" },
				},
			],
		};
	}

	return {
		tooltip,
		series: [
			{
				type: "treemap",
				name,
				data: tree,
				roam: false,
				nodeClick: "zoomToNode",
				leafDepth: 2,
				top: 0,
				left: 0,
				right: 0,
				bottom: 32,
				breadcrumb: {
					bottom: 0,
					itemStyle: { color: palette.surface, borderColor: palette.border, textStyle: { color: palette.text } },
					emphasis: { itemStyle: { color: palette.steps[0] } },
				},
				label: { color: palette.text, fontSize: 11 },
				upperLabel: { show: true, height: 18, color: palette.text, fontSize: 11 },
				itemStyle: { borderColor: palette.background, borderWidth: 2, gapWidth: 2 },
				levels: depthLevels(palette).map((level) => ({ ...level, itemStyle: { ...level.itemStyle, borderWidth: 2, gapWidth: 2 } })),
			},
		],
	};
}
