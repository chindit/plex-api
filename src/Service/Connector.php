<?php
declare(strict_types=1);

namespace Chindit\PlexApi\Service;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @internal
 */
class Connector
{
	private HttpClientInterface $connection;

	/**
	 * @param array<string, mixed> $options
	 */
	public function __construct(
		private string $host,
		private string $token,
		private int $port = 32400,
		private array $options = [],
	)
	{
		$this->connection = HttpClient::create(array_merge([
			'query' => ['X-Plex-Token' => $this->token],
			'max_redirects' => 5,
			'timeout' => 10,
			'verify_host' => false,
			'base_uri' => $this->host . ':' . $this->port,
		], $this->options));
	}

	public function get(string $endpoint): \SimpleXMLElement
	{
		$response = simplexml_load_string($this->connection->request('GET', $endpoint)->getContent());
		if (!$response) {
			throw new \UnexpectedValueException(sprintf('Unable to call %s endpoint on plex server', $endpoint));
		}

		return $response;
	}

    public function getRaw(string $endpoint): string
    {
        return $this->connection->request('GET', $endpoint)->getContent();
    }

    public function export(): string
    {
        return json_encode([
            'host' => $this->host,
            'key' => $this->token,
            'port' => $this->port,
            'options' => $this->options
        ], JSON_THROW_ON_ERROR);
    }
}
