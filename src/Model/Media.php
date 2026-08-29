<?php

namespace Chindit\PlexApi\Model;

abstract class Media
{
	protected string $ratingKey;
	protected string $studio;
	protected string $contentRating;
	protected string $rating;
	protected string $title;
	protected string $summary;
	protected string $year = '';
	protected string $thumb = '';
	protected string $duration;
	protected \DateTimeImmutable $createdAt;
	/** @var array<int, string> */
	protected array $actors;
	/** @var array<int, string> */
	protected array $genres;
	protected string $audioCodec;
	protected string $audioProfile;
	protected string $videoCodec;
	protected string $videoProfile;
	protected float $aspectRatio;
	protected int $bitrate;
	protected int $width;
	protected int $height;
	protected string $videoResolution;
	protected string $container;
	protected string $frameRate;

	public function getRatingKey(): string
	{
		return $this->ratingKey;
	}

	public function getAudioCodec(): string
	{
		return $this->audioCodec;
	}

	public function getAudioProfile(): string
	{
		return $this->audioProfile;
	}

	public function getVideoCodec(): string
	{
		return $this->videoCodec;
	}

	public function getVideoProfile(): string
	{
		return $this->videoProfile;
	}

	public function getAspectRatio(): float
	{
		return $this->aspectRatio;
	}

	public function getBitrate(): int
	{
		return $this->bitrate;
	}

	public function getWidth(): int
	{
		return $this->width;
	}

	public function getHeight(): int
	{
		return $this->height;
	}

	public function getVideoResolution(): string
	{
		return $this->videoResolution;
	}

	public function getContainer(): string
	{
		return $this->container;
	}

	public function getFrameRate(): string
	{
		return $this->frameRate;
	}

	public function getId(): int
	{
		return (int)$this->ratingKey;
	}

	public function getStudio(): string
	{
		return $this->studio;
	}

	public function getContentRating(): string
	{
		return $this->contentRating;
	}

	public function getRating(): string
	{
		return $this->rating;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getSummary(): string
	{
		return $this->summary;
	}

	public function getYear(): string
	{
		return $this->year;
	}

	public function getThumb(): string
	{
		return $this->thumb;
	}

	public function getDuration(): int
	{
		return (int)round(((int)($this->duration ?? 0)/1000));
	}

	public function getCreatedAt(): \DateTimeImmutable
	{
		return $this->createdAt;
	}

	/**
	 * @return array<string>
	 */
	public function getActors(): array
	{
		return $this->actors;
	}

	/**
	 * @return array<string>
	 */
	public function getGenres(): array
	{
		return $this->genres;
	}
}
