<?php

namespace App\Enum;

/**
 * Define the possible Map providers and their layer & attributions.
 * (will be used later in Livewire).
 */
enum MapProviders: string
{
	case Wikimedia = 'Wikimedia';
	case OpenStreetMapOrg = 'OpenStreetMap.org';
	case OpenStreetMapDe = 'OpenStreetMap.de';
	case OpenStreetMapFr = 'OpenStreetMap.fr';
	case RRZE = 'RRZE';

	public function getLayer(): string
	{
		return match ($this) {
			self::Wikimedia => 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png',
			self::OpenStreetMapOrg => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
			self::OpenStreetMapDe => 'https://tile.openstreetmap.de/{z}/{x}/{y}.png ',
			self::OpenStreetMapFr => 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png ',
			self::RRZE => 'https://{s}.osm.rrze.fau.de/osmhd/{z}/{x}/{y}.png',
		};
	}

	public function getAtributionHtml(): string
	{
		return match ($this) {
			self::Wikimedia => '<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>',
			self::OpenStreetMapOrg => '&copy; <a href="https://openstreetmap.org/copyright">' . __('lychee.OSM_CONTRIBUTORS') . '</a>',
			self::OpenStreetMapDe => '&copy; <a href="https://openstreetmap.org/copyright">' . __('lychee.OSM_CONTRIBUTORS') . '</a>',
			self::OpenStreetMapFr => '&copy; <a href="https://openstreetmap.org/copyright">' . __('lychee.OSM_CONTRIBUTORS') . '</a>',
			self::RRZE => '&copy; <a href="https://openstreetmap.org/copyright">' . __('lychee.OSM_CONTRIBUTORS') . '</a>',
		};
	}
}
