<?php
declare(strict_types=1);

namespace Chindit\PlexApi\Model;

use Chindit\Collection\Collection;
use Chindit\PlexApi\Service\Connector;

final class Server
{
    /**
     * @var Collection<Connection>
     */
    private Collection $connections;
	public function __construct(
        public readonly string $identifier,
		public readonly string $name,
		public readonly string $version,
        public readonly string $token,
	)
	{
        $this->connections = new Collection();
    }

    public function addConnection(Connection $connection): self
    {
        $this->connections->push($connection);

        return $this;
    }

    /**
     * @return Collection<Connection>
     */
    public function getConnections(): Collection
    {
        return $this->connections;
    }

    public static function fetchByHost(string $host, string $token, int $port = 32400): ?self
    {
        try {
            $connection = new Connection($host, $port, false, false, false);
            $response = new Connector($token, $connection, [])->get('/identity');

            return new Server(
                (string)$response['machineIdentifier'],
                'My Plex',
                (string)$response['version'],
                $token,
            )
                ->addConnection($connection);
        } catch (\Throwable) {
            return null;
        }
    }
}
