/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * ECharts registration for the Insights page (Feature 085, ADR-085-04).
 *
 * Only the chart types and components in use are registered, with the SVG
 * renderer. Imported from the Insights view only, so ECharts stays in that
 * route's lazy chunk.
 */
import { use } from "echarts/core";
import { BarChart, HeatmapChart, LineChart, ScatterChart, SunburstChart, TreemapChart } from "echarts/charts";
import { CalendarComponent, DataZoomComponent, GridComponent, TooltipComponent, VisualMapComponent } from "echarts/components";
import { SVGRenderer } from "echarts/renderers";

use([
	BarChart,
	HeatmapChart,
	LineChart,
	ScatterChart,
	SunburstChart,
	TreemapChart,
	CalendarComponent,
	DataZoomComponent,
	GridComponent,
	TooltipComponent,
	VisualMapComponent,
	SVGRenderer,
]);
