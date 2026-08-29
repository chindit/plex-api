<?php

namespace Chindit\PlexApi;

use Chindit\PlexApi\Enum\LibraryType;
use Chindit\PlexApi\Exceptions\UnreachableServerException;
use Chindit\PlexApi\Model\Album;
use Chindit\PlexApi\Model\Artist;
use Chindit\PlexApi\Model\Connection;
use Chindit\PlexApi\Model\Episode;
use Chindit\PlexApi\Model\Library;
use Chindit\PlexApi\Model\Movie;
use Chindit\PlexApi\Model\MusicTrack;
use Chindit\PlexApi\Model\Server;
use Chindit\PlexApi\Model\Show;
use Chindit\PlexApi\Service\Connector;
use Chindit\PlexApi\Service\XmlParser;
use Symfony\Component\HttpClient\Exception\ClientException;

final readonly class PlexServer
{
    private Connector $connector;

    public function __construct(
        private Server $server
    ) {
        foreach ($this->server->getConnections() as $connection) {
            if (!$connection->isLocal) {
                $connector = new Connector($this->server->token, $connection);
                if(!$connector->ping()) {
                    continue;
                }
                $this->connector = $connector;

                return;
            }
        }

        throw new UnreachableServerException(sprintf('Unable to reach %s', $this->server->name));
    }

    public function sessionsCount(): int
    {
        $serverResponse = $this->connector->get('/status/sessions');

        return (int)$serverResponse->attributes()['size'];
    }

    /**
     * @return array<int, Library>
     */
    public function libraries(): array
    {
        $serverResponse = $this->connector->get('/library/sections');
        $libraries = [];

        foreach ($serverResponse->Directory as $library) {
            $libraries[] = new Library(
                (int)$library->attributes()['key'],
                (string)$library->attributes()['allowSync'] === '1',
                (string)$library->attributes()['thumb'],
                match ((string)$library->attributes()['type']) {
                    'movie' => LibraryType::Movie,
                    'show' => LibraryType::Show,
                    'artist' => LibraryType::Music,
                    default => throw new \InvalidArgumentException(sprintf("Collection type %s in not supported", $library->attributes()['type']))
                },
                (string)$library->attributes()['title'],
                (string)$library->attributes()['language'],
                (new \DateTimeImmutable())->setTimestamp((int)$library->attributes()['createdAt']),
                (new \DateTimeImmutable())->setTimestamp((int)$library->attributes()['updatedAt']),
                (new \DateTimeImmutable())->setTimestamp((int)$library->attributes()['scannedAt']),
                (string)$library->Location->attributes()['Location']
            );
        }

        return $libraries;
    }

    /**
     * @return array<int, Movie|Show|Artist>
     */
    public function library(int $libraryId, bool $unwatchedOnly = false): array
    {
        $url = '/library/sections/' . $libraryId . '/all';
        if ($unwatchedOnly) {
            $url .= '?unwatched=1';
        }
        $serverResponse = $this->connector->get($url);
        $items = [];

        /** @var \SimpleXMLElement $item */
        foreach ($serverResponse as $item) {
            switch ((string)$item->attributes()['type']) {
                case 'movie':
                    $items[] = new Movie(array_merge(
                        XmlParser::getGlobalAttributes($item),
                        XmlParser::getTechnicalAttributes($item),
                    ));
                    break;
                case 'show':
                    $items[] = new Show(array_merge(
                        XmlParser::getGlobalAttributes($item),
                        [
                            'seasonCount' => (int)$item->attributes()['seasonCount'],
                            'totalEpisodes' => (int)$item->attributes()['totalEpisodes'],
                            'episodes' => $this->getShowEpisodes((int)$item->attributes()['ratingKey']),
                        ],
                    ));
                    break;
                case 'artist':
                    $items[] = new Artist(array_merge(
                        XmlParser::getArtist($item),
                        [
                            'albums' => $this->getArtistAlbums((int)$item->attributes()['ratingKey']),
                        ],
                    ));
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf("Element type %s in not supported", $item->attributes()['type']));
            }
        }

        return $items;
    }

    /** @return array<Episode> */
    private function getShowEpisodes(int $showId): array
    {
        $serverResponse = $this->connector->get('/library/metadata/' . $showId . '/allLeaves');
        $episodes = [];

        /** @var \SimpleXMLElement $episode */
        foreach ($serverResponse as $episode) {
            if ((string)$episode->attributes()['type'] !== 'episode') {
                throw new \InvalidArgumentException(sprintf("Show type %s in not supported", $episode->attributes()['type']));
            }

            $episodes[] = new Episode(array_merge(
                XmlParser::getGlobalAttributes($episode),
                XmlParser::getTechnicalAttributes($episode),
                [
                    'season' => (int)$episode->attributes()['parentIndex'],
                    'episode' => (int)$episode->attributes()['index'],
                ],
            ));
        }

        return $episodes;
    }

    /** @return Album[] */
    private function getArtistAlbums(int $artistId): array
    {
        $serverResponse = $this->connector->get('/library/metadata/' . $artistId . '/children');
        $albums = [];

        /** @var \SimpleXMLElement $album */
        foreach ($serverResponse as $album) {
            if ((string)$album->attributes()['type'] !== 'album') {
                throw new \InvalidArgumentException(sprintf('Album of type %s is not supported', $album->attributes()['type']));
            }

            $albums[] = new Album(array_merge(
                XmlParser::getAlbum($album),
                ['tracks' => $this->getTracksForAlbum((int)$album->attributes()['ratingKey'])]
            ));
        }

        return $albums;
    }

    /** @return MusicTrack[] */
    public function getTracksForAlbum(int $albumId): array
    {
        $serverResponse = $this->connector->get('/library/metadata/' . $albumId . '/children');
        $tracks = [];

        /** @var \SimpleXMLElement $track */
        foreach ($serverResponse as $track) {
            if ((string)$track->attributes()['type'] !== 'track') {
                throw new \InvalidArgumentException(sprintf('Album of type %s is not supported', $track->attributes()['type']));
            }

            $tracks[] = new MusicTrack(XmlParser::getTrack($track));
        }

        return $tracks;
    }

    public function refreshMovie(Movie $movie): ?Movie
    {
        try {
            $serverResponse = $this->connector->get('/library/metadata/' . $movie->getRatingKey());
        } catch (ClientException) {
            return null;
        }

        $item = $serverResponse->children()[0];
        return new Movie(array_merge(XmlParser::getGlobalAttributes($item), XmlParser::getTechnicalAttributes($item)));
    }

    public function getFromKey(int|string $ratingKey): ?Movie
    {
        return $this->refreshMovie(new Movie(['ratingKey' => $ratingKey]));
    }

    public function getThumb(string $thumb): string
    {
        return $this->connector->getRaw($thumb);
    }
}
