<?php

namespace Chindit\PlexApi\Service;

use Chindit\Collection\Collection;

/**
 * @internal
 */
class XmlParser
{
	/**
	 * @return array<string, mixed>
	 */
	public static function getGlobalAttributes(\SimpleXMLElement $item): array
	{
		return array_merge(
			array_values((array)$item->attributes())[0],
			[
				'genres' => (new Collection($item->xpath('Genre') ?? []))->map(fn(\SimpleXMLElement $element) => (array)$element->attributes())->flatten()->toArray(),
				'directors' => (new Collection($item->xpath('Director') ?? []))->map(fn(\SimpleXMLElement $element) => (array)$element->attributes())->flatten()->toArray(),
				'writers' => (new Collection($item->xpath('Writer') ?? []))->map(fn(\SimpleXMLElement $element) => (array)$element->attributes())->flatten()->toArray(),
				'countries' => (new Collection($item->xpath('Country') ?? []))->map(fn(\SimpleXMLElement $element) => (array)$element->attributes())->flatten()->toArray(),
				'actors' => (new Collection($item->xpath('Role') ?? []))->map(fn(\SimpleXMLElement $element) => (array)$element->attributes())->flatten()->toArray(),
			]
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function getTechnicalAttributes(\SimpleXMLElement $item): array
	{
		/** @var \SimpleXMLElement $mediaAttributes */
		$mediaAttributes = $item->Media->attributes();

		return [
			'bitrate' => (int)$mediaAttributes['bitrate'],
			'width' => (int)$mediaAttributes['width'],
			'height' => (int)$mediaAttributes['height'],
			'aspectRatio' => (float)$mediaAttributes['aspectRatio'],
			'audioChannels' => (int)$mediaAttributes['audioChannels'],
			'audioCodec' => (string)$mediaAttributes['audioCodec'],
			'videoCodec' => (string)$mediaAttributes['videoCodec'],
			'resolution' => (int)$mediaAttributes['videoResolution'],
			'container' => (string)$mediaAttributes['container'],
			'framerate' => (string)$mediaAttributes['videoFrameRate'],
			'profile' => (string)$mediaAttributes['videoProfile'],
		];
	}

	/**
	* @return array<string, mixed>
	*/
	public static function getArtist(\SimpleXMLElement $item): array
	{
		/** @var \SimpleXMLElement $mediaAttributes */
		$mediaAttributes = $item->attributes();

		return [
			'artist' => (string)$mediaAttributes['title'],
			'description' => (string)$mediaAttributes['summary'],
			'thumb' => (string)$mediaAttributes['thumb'],
			'genres' => (new Collection($item->xpath('Genre') ?? []))->map(fn(\SimpleXMLElement $element) => (array)$element->attributes())->flatten()->toArray(),
			'countries' => (new Collection($item->xpath('Country') ?? []))->map(fn(\SimpleXMLElement $element) => (array)$element->attributes())->flatten()->toArray(),
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function getAlbum(\SimpleXMLElement $album): array
	{
		/** @var \SimpleXMLElement $mediaAttributes */
		$mediaAttributes = $album->attributes();

		return [
			'studio' => (string)$mediaAttributes['studio'],
			'title' => (string)$mediaAttributes['title'],
			'thumb' => (string)$mediaAttributes['thumb'],
			'releasedAt' => \DateTime::createFromFormat('Y-m-d', (string)$mediaAttributes['originallyAvailableAt']),
			'genres' => (new Collection($album->xpath('Genre') ?? []))->map(fn(\SimpleXMLElement $element) => (array)$element->attributes())->flatten()->toArray(),
			'directors' => (new Collection($album->xpath('Director') ?? []))->map(fn(\SimpleXMLElement $element) => (array)$element->attributes())->flatten()->toArray(),
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function getTrack(\SimpleXMLElement $track): array
	{
		/** @var \SimpleXMLElement $mediaAttributes */
		$mediaAttributes = $track->Media->attributes();

		return [
			'duration' => (int)$mediaAttributes['duration'],
			'title' => (string)$track->attributes()['title'],
			'thumb' => (string)$track->attributes()['thumb'],
			'bitrate' => (int)$mediaAttributes['bitrate'],
			'audioChannels' => (int)$mediaAttributes['audioChannels'],
			'audioCodec' => (string)$mediaAttributes['audioCodec'],
			'container' => (string)$mediaAttributes['container']
		];
	}
}
