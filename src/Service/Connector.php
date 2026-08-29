<?php
declare(strict_types=1);

namespace Chindit\PlexApi\Service;


use Chindit\PlexApi\Model\Connection;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @internal
 */
class Connector
{
	private HttpClientInterface $httpClient;

	/**
	 * @param array<string, mixed> $options
	 */
	public function __construct(
        private readonly string $token,
		private readonly Connection $connection,
		private array $options = [],
	)
	{
		$this->httpClient = HttpClient::create(array_merge([
			'query' => ['X-Plex-Token' => $this->token],
			'max_redirects' => 5,
			'timeout' => 10,
			'verify_host' => false,
			'base_uri' => $this->getBaseUri(),
		], $this->options));
	}

    public function ping(): bool
    {
        try {
            $identity = $this->get('/identity');

            return isset($identity['machineIdentifier'])
                && (string) $identity['machineIdentifier'] !== '';
        } catch (\Throwable) {
            return false;
        }
    }

	private function getBaseUri(): string
	{
		$host = rtrim($this->connection->host, '/');

		// Plex returns connections as complete URIs (often including the port).
		// Do not append the port a second time in that case.
		if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $host) === 1) {
			$parsedHost = parse_url($host);

			if (is_array($parsedHost) && isset($parsedHost['port'])) {
				return $host;
			}
		}

		return $host;
	}

	public function get(string $endpoint): \SimpleXMLElement
	{
		$response = simplexml_load_string($this->httpClient->request('GET', $endpoint)->getContent());
		if (!$response) {
			throw new \UnexpectedValueException(sprintf('Unable to call %s endpoint on plex server', $endpoint));
		}

		return $response;
	}

    public function getRaw(string $endpoint): string
    {
        return $this->httpClient->request('GET', $endpoint)->getContent();
    }
}
