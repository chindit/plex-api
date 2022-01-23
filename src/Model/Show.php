<?php
declare(strict_types=1);

namespace Chindit\PlexApi\Model;

final class Show extends Media
{
	public readonly int $seasonCount;
	public readonly int $totalEpisodes;
	public readonly array $episodes;

	public static function hydrate(array $values): self
	{
		$show = new self();
		foreach ($values as $name => $value)
		{
			if ($name === 'addedAt') {
				$name = 'createdAt';
				$value = (new \DateTimeImmutable())->setTimestamp((int)$value);
			}
			if (property_exists($show, $name)) {
				$show->$name = $value;
			}
		}

		return $show;
	}

	public function getSeasonCount(): int
	{
		return $this->seasonCount;
	}

	public function getTotalEpisodes(): int
	{
		return $this->totalEpisodes;
	}

	/**
	 * @return array<Episode>
	 */
	public function getEpisodes(): array
	{
		return $this->episodes;
	}
}
