const Constants = {
	API_URL: "/api/v2/",
	API_URL_V3: "/api/v3/",
	BASE_URL: document.querySelector("base")?.getAttribute("href") || "",
	getApiUrl(): string {
		return this.BASE_URL + this.API_URL;
	},
	getApiUrlV3(): string {
		return this.BASE_URL + this.API_URL_V3;
	},
};

export type PaginatedResponse<T> = {
	data: T[];
	links: { url: string | null; label: string; active: boolean }[];
	meta: {
		current_page: number;
		first_page_url: string;
		from: number | null;
		last_page: number;
		last_page_url: string;
		next_page_url: string | null;
		path: string;
		per_page: number;
		prev_page_url: string | null;
		to: number | null;
		total: number;
	};
};

export default Constants;
