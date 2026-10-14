import { trans } from "laravel-vue-i18n";

type ModerationEvent = App.Http.Resources.Models.ModerationEventResource;

/** Label of the event: Held or Approved. */
export function moderationEventLabel(event: ModerationEvent): string {
	return event.event === "held" ? trans("moderation.event_held") : trans("moderation.event_approved");
}

/** Who acted: the admin, "Upload" or "NSFW scan" for the system, "—" for a deleted admin. */
export function moderationEventActor(event: ModerationEvent): string {
	if (event.source === "upload") {
		return trans("moderation.source_upload");
	}
	if (event.source === "nsfw_scan") {
		return trans("moderation.source_nsfw_scan");
	}
	return event.username ?? "—";
}
