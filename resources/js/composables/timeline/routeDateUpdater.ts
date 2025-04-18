import { useIntersectionObserver } from "@vueuse/core";
import { Ref } from "vue";

export function useRouteDateUpdater(sentinel: Ref, loadMore: () => void, loadDate: (date: string | undefined) => void) {
	function getCurrentSectionInView(base: HTMLElement) {
		let sections = document.querySelectorAll("[data-type='timelineBlock']");
		for (let i = 0; i < sections.length; i++) {
			const section = sections[i] as HTMLElement;
			const currentPosition = base.scrollTop;
			const startAt = section.getBoundingClientRect().top + currentPosition;
			const endAt = startAt + section.offsetHeight;
			const isInView = currentPosition >= startAt && currentPosition < endAt;
			if (isInView) {
				return section;
			}
		}
	}

	function registerScrollSpy() {
		const base = document.querySelectorAll(".overflow-y-auto")[0] as HTMLElement;
		base.addEventListener("scroll", () => {
			const section = getCurrentSectionInView(base);
			if (section) {
				loadDate(section.dataset.date);
			}
		});
	}

	function registerSentinel() {
		const { stop } = useIntersectionObserver(sentinel, ([{ isIntersecting }]) => {
			if (isIntersecting) {
				loadMore();
			}
		});
	}

	return {
		registerSentinel,
		registerScrollSpy,
	};
}
