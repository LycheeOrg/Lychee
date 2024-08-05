const Constants = {
	API_URL: "/api/v2/",
};

export type PaginatedResponse<T> = {
	data: T[];
	links: {url: string | null;label: string;active: boolean;}[];
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
	}
};

export default Constants;
