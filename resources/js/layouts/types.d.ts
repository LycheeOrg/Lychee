export interface ChildNodeWithDataStyle extends ChildNode {
	dataset: { width: number; height: number };
	style: { top: string; width: string; height: string; left: string };
}

export interface Column {
	height: number;
	left: number;
}
