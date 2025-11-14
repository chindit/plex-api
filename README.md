# Plex API

### Installation

```shell
composer require chindit/plex-api
```

### Usage

Create a new instance of `PlexServer`

Minimal parameters to pass are server host and token.
```php
$plex = new Chindit\PlexServer('http://path.to.my.plex', 'MyPlexToken');
```
You can also provide a specific port as third argument (default is 32400) and 
some options as fourth parameters.  Options must be suppored by Symfony's HTTP client.

Most common parameters are `max_redirects` and `timeout`.

In this case, server initialization will look like this:

```php
$plex = new Chindit\PlexServer('http://path.to.my.plex', 'MyPlexToken', 32400, ['timeout' => 10]);
```

To find you plex token, [check this article](https://support.plex.tv/articles/204059436-finding-an-authentication-token-x-plex-token/)

### Methods available

Once your `$plexserver` instance created, following methods are available:

* `checkConnection(): bool` Checks if connection can be made to your Plex instance.

* `servers(): array<Server>` Returns the list of active servers for your Plex instance.
   
   Response is an array of `Chindit\Model\Server` objects
* `sessionsCount(): int` Returns the number of active sessions on the server.  An active session is a device streaming a media.
* `libraries(): array<Library>` Returns all you libraries.  A Plex library is a general section like your «Movies» or «Shows» categories.  All your medias are contained in libraries.
* `library(int $libraryId): array<Movie|Show>`  Return all the media contained in a specific library.  Library id can be obtained by a `getId()` on a `Library` object.
* `getFromKey(int|string $ratingKey): ?Movie` Return 0 or 1 movie based on the given rating key.
* `refreshMovie(Movie $movie): ?Movie` Refresh movie information from Plex server. Will return `null` if movie is not longer found on Plex.

   Example:
   ```php
   $myLibraries = $plexServer->libraries();
   $theLibraryIWant = $myLibraries[0]; // Choose any library you want;
   $myMedias = $plexServer->library($theLibraryIWant->getId());
   ```

    Response is an array of `Chindit\Model\Movie` and `Chindit\Model\Show` objects.

### Need help?

If you need a specific call, have a suggestion or found a bug, do not hesitate tot leave a comment on the `Issue` tab.
