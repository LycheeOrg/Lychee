export type SplitData<T> = {
	header: string;
	data: T[];
	iter: number;
};

export function useSplitter() {
	function spliter<T>(data: T[], mapper: (d: T) => string, formatter: (d: T) => string, start: number = 0): SplitData<T>[] {
		const ret = [] as SplitData<T>[];

		const headers: string[] = [...new Set(data.map(mapper))];
		headers.forEach((h) => {
			const headerData = data.filter((d) => mapper(d) === h);
			ret.push({ header: formatter(headerData[0]), data: headerData, iter: 0 });
		});

		// loop over all the shared albums to prep the indexes.
		let idx = 0;
		let sum = start;
		for (idx = 0; idx < ret.length; idx++) {
			ret[idx].iter = sum;
			sum += ret[idx].data.length;
		}

		return ret;
	}

	return {
		spliter,
	};
}
