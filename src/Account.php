<?php

namespace Chindit\PlexApi;

use Chindit\Collection\Collection;
use Chindit\PlexApi\Exceptions\InvalidAccountException;
use Chindit\PlexApi\Model\Connection;
use Chindit\PlexApi\Model\Server;
use Symfony\Component\HttpClient\HttpClient;

class Account
{
    private static string $plexUrl = 'https://clients.plex.tv/';

    /** @var Collection<Server> */
    private Collection $servers;
    public function __construct(
        private readonly string $token,
        private readonly ?string $url = null,
        private readonly int $port = 32400
    )
    {
        $this->servers = new Collection();
        if (!empty($this->url)) {
            $server = Server::fetchByHost($this->url, $this->token, $this->port);

            if(!$server) {
                throw new InvalidAccountException($this->url . ':' . $this->port);
            }

            $this->servers->push($server);
        }
    }

    /**
     * @return Collection<Server>
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getServerList(): Collection
    {
        if ($this->servers->isNotEmpty()) {
            return $this->servers;
        }

        $serverList = HttpClient::createForBaseUri(self::$plexUrl)
            ->withOptions(['headers' => [
                'X-Plex-Token' => $this->token,
                'X-Plex-Client-Identifier' => 'PlexApi Connector'
            ]])
            ->request('GET', '/api/v2/resources');

        $XmlResponse = new \SimpleXMLElement($serverList->getContent());
        foreach ($XmlResponse->resource as $connection) {
            if (!str_contains((string)$connection['provides'], 'server')) {
                continue;
            }

            $server = new Server(
                (string)$connection['clientIdentifier'],
                (string)$connection['sourceTitle'] . ' - ' . (string)$connection['name'],
                (string)$connection['productVersion'],
                (string)$connection['accessToken'],
            );

            foreach ($connection->connections->connection as $serverAccess) {
                $server->addConnection(new Connection(
                    $serverAccess->attributes()->uri,
                    (int) $serverAccess->attributes()->port,
                    (string)$serverAccess['local'] === '1',
                    (string)$serverAccess['relay'] === '1',
                    (string)$serverAccess['IPv6'] === '1'
                ));
            }

            $this->servers->push($server);
        }

        return $this->servers;
    }

    public function getServer(?string $identifier = null): PlexServer
    {
        $servers = $this->getServerList();

        if ($identifier === null && $servers->count() === 1 && $servers->first()) {
            return new PlexServer($servers->first());
        }

        foreach ($servers as $server) {
            if ($server->identifier === $identifier) {
                return new PlexServer($server);
            }
        }

        throw new \InvalidArgumentException(
            $identifier === null
                ? 'You must provide an identifier when multiple servers are reachable'
                : sprintf('Unable to locate server : %s', $identifier)
        );
    }
}
