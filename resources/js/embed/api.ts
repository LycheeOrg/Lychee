import type { EmbedApiResponse, EmbedStreamApiResponse } from "./types";

/**
 * API client for fetching album data from Lychee
 */
export class EmbedApiClient {
	private apiUrl: string;

	constructor(apiUrl: string) {
		this.apiUrl = apiUrl;
	}

	/**
	 * Fetch album data for embedding
	 *
	 * @param albumId Album ID to fetch
	 * @param limit   Optional maximum number of photos to fetch (1-500)
	 * @param offset  Optional number of photos to skip (default: 0)
	 * @param sort    Optional sort order ('asc' or 'desc')
	 * @param author  Optional username to filter photos by uploader
	 * @returns Album and photos data
	 * @throws Error if fetch fails or album is not accessible
	 */
	fetchAlbum(albumId: string, limit?: number, offset?: number, sort?: "asc" | "desc", author?: string): Promise<EmbedApiResponse> {
		// Build URL with optional pagination parameters
		const url = new URL(`${this.apiUrl}/api/v2/Embed/${encodeURIComponent(albumId)}`);
		if (limit !== undefined) {
			url.searchParams.set("limit", String(limit));
		}
		if (offset !== undefined) {
			url.searchParams.set("offset", String(offset));
		}
		if (sort !== undefined) {
			url.searchParams.set("sort", sort);
		}
		if (author !== undefined) {
			url.searchParams.set("author", author);
		}

		return fetch(url.toString(), {
			method: "GET",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/json",
			},
			mode: "cors",
			credentials: "omit", // Don't send cookies for embed requests
		})
			.then((response) => {
				if (!response.ok) {
					return this.handleErrorResponse(response);
				}
				return response.json();
			})
			.then((data) => {
				return this.validateApiResponse(data);
			})
			.catch((error) => {
				if (error instanceof Error) {
					throw new Error(`Failed to fetch album: ${error.message}`);
				}
				throw new Error("Failed to fetch album: Unknown error");
			});
	}

	/**
	 * Fetch public photo stream for embedding
	 *
	 * @param limit  Optional maximum number of photos to fetch (1-500, default: 100)
	 * @param offset Optional number of photos to skip (default: 0)
	 * @param sort   Optional sort order ('asc' or 'desc', default: 'desc')
	 * @param author Optional username to filter photos by uploader
	 * @returns Public photos data
	 * @throws Error if fetch fails
	 */
	fetchStream(limit?: number, offset?: number, sort?: "asc" | "desc", author?: string): Promise<EmbedStreamApiResponse> {
		// Build URL with optional pagination parameters
		const url = new URL(`${this.apiUrl}/api/v2/Embed/stream`);
		if (limit !== undefined) {
			url.searchParams.set("limit", String(limit));
		}
		if (offset !== undefined) {
			url.searchParams.set("offset", String(offset));
		}
		if (sort !== undefined) {
			url.searchParams.set("sort", sort);
		}
		if (author !== undefined) {
			url.searchParams.set("author", author);
		}

		return fetch(url.toString(), {
			method: "GET",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/json",
			},
			mode: "cors",
			credentials: "omit", // Don't send cookies for embed requests
		})
			.then((response) => {
				if (!response.ok) {
					return this.handleStreamErrorResponse(response);
				}
				return response.json();
			})
			.then((data) => {
				return this.validateStreamApiResponse(data);
			})
			.catch((error) => {
				if (error instanceof Error) {
					throw new Error(`Failed to fetch public stream: ${error.message}`);
				}
				throw new Error("Failed to fetch public stream: Unknown error");
			});
	}

	/**
	 * Handle HTTP error responses
	 *
	 * @param response HTTP response object
	 * @throws Error with appropriate message based on status code
	 */
	private handleErrorResponse(response: Response): Promise<never> {
		const status = response.status;

		switch (status) {
			case 404:
				throw new Error("Album not found. Please check the album ID.");
			case 403:
				throw new Error("Album is not publicly accessible. Only public albums without password protection can be embedded.");
			case 500:
				throw new Error("Server error. Please try again later.");
			case 503:
				throw new Error("Service unavailable. Please try again later.");
			default:
				throw new Error(`HTTP ${status}: Failed to fetch album data.`);
		}
	}

	/**
	 * Validate API response structure
	 *
	 * @param data Response data to validate
	 * @returns Validated API response
	 * @throws Error if response structure is invalid
	 */
	private validateApiResponse(data: unknown): EmbedApiResponse {
		if (!data || typeof data !== "object") {
			throw new Error("Invalid API response: Expected object");
		}

		const response = data as Record<string, unknown>;

		// Validate album object
		if (!response.album || typeof response.album !== "object") {
			throw new Error("Invalid API response: Missing or invalid album data");
		}

		const album = response.album as Record<string, unknown>;
		if (!album.id || typeof album.id !== "string") {
			throw new Error("Invalid API response: Album missing ID");
		}

		// Validate photos array
		if (!Array.isArray(response.photos)) {
			throw new Error("Invalid API response: Photos must be an array");
		}

		// Basic validation passed, return as typed response
		return data as EmbedApiResponse;
	}

	/**
	 * Handle HTTP error responses for stream endpoint
	 *
	 * @param response HTTP response object
	 * @throws Error with appropriate message based on status code
	 */
	private handleStreamErrorResponse(response: Response): Promise<never> {
		const status = response.status;

		switch (status) {
			case 500:
				throw new Error("Server error. Please try again later.");
			case 503:
				throw new Error("Service unavailable. Please try again later.");
			default:
				throw new Error(`HTTP ${status}: Failed to fetch public stream data.`);
		}
	}

	/**
	 * Validate stream API response structure
	 *
	 * @param data Response data to validate
	 * @returns Validated stream API response
	 * @throws Error if response structure is invalid
	 */
	private validateStreamApiResponse(data: unknown): EmbedStreamApiResponse {
		if (!data || typeof data !== "object") {
			throw new Error("Invalid API response: Expected object");
		}

		const response = data as Record<string, unknown>;

		// Validate photos array
		if (!Array.isArray(response.photos)) {
			throw new Error("Invalid API response: Photos must be an array");
		}

		// Basic validation passed, return as typed response
		return data as EmbedStreamApiResponse;
	}
}

/**
 * Create a new API client instance
 *
 * @param apiUrl Base URL of the Lychee API
 * @returns API client instance
 */
export function createApiClient(apiUrl: string): EmbedApiClient {
	return new EmbedApiClient(apiUrl);
}
