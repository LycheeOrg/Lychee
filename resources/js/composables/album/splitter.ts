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

	function verifyOrder(is_debug: boolean, data: { id: string }[], splitData: SplitData<{ id: string }>[]) {
		if (!is_debug) {
			return;
		}

		const dataMap = new Map<string, number>();
		data.forEach((d, i) => {
			dataMap.set(d.id, i);
		});

		let check = false;
		splitData.forEach((chunk) => {
			chunk.data.forEach((d, idx) => {
				const expected = dataMap.get(d.id);
				if (expected === undefined) {
					console.error(`Data not found in original data for id ${d.id} (WTF??)`);
					check = true;
				}
				const candidate = chunk.iter + idx;
				if (expected !== candidate) {
					console.error(`Data mismatch for id ${d.id} (expected ${expected}, got ${candidate})`);
					check = true;
				}
			});
		});

		if (check) {
			alert("Data mismatch found in splitter, please check the console logs and contact the developer.");
		}
	}

	return {
		spliter,
		verifyOrder,
	};
}
