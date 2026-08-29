# Plex API

## Installation

```shell
composer require chindit/plex-api
```

## Usage

Create an account with a Plex token. The account can discover the Plex servers
available to that token:

```php
use Chindit\PlexApi\Account;

$account = new Account('MyPlexToken');
$servers = $account->getServerList();
$plexServer = $account->getServer();
```

When several servers are available, pass the server identifier:

```php
$server = $servers->first();
$plexServer = $account->getServer($server->identifier);
```

`getServer()` selects a reachable connection and uses the server-specific
access token returned by Plex.

It is also possible to connect directly to a known server:

```php
$account = new Account('MyPlexToken', 'https://path.to.my.plex', 32400);
$plexServer = $account->getServer();
```

The port defaults to `32400`. To find a Plex token, see the [Plex
documentation](https://support.plex.tv/articles/204059436-finding-an-authentication-token-x-plex-token/).

## Available methods

Once `$plexServer` has been created:

- `sessionsCount(): int` returns the number of active streaming sessions.
- `libraries(): array<Library>` returns the libraries available to the account.
- `library(int $libraryId, bool $unwatchedOnly = false): array` returns the media in a library.
- `getTracksForAlbum(int $albumId): array` returns the tracks in an album.
- `getFromKey(int|string $ratingKey): ?Movie` returns a movie by its rating key.
- `refreshMovie(Movie $movie): ?Movie` refreshes a movie from Plex.
- `getThumb(string $thumb): string` downloads a thumbnail.

Example:

```php
$libraries = $plexServer->libraries();
$library = $libraries[0];
$media = $plexServer->library($library->getId());
```

## Need help?

For a missing call or a bug, open an issue in the repository.
