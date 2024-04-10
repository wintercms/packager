<?php

namespace Winter\Packager\Package;

use Composer\MetadataMinifier\MetadataMinifier;
use Composer\Semver\VersionParser;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Winter\Packager\Enums\ListType;
use Winter\Packager\Exceptions\PackagistException;
use Winter\Packager\Storage\Storage;

/**
 * Packagist class.
 *
 * Handles connecting to and making requests against the Packagist API. The Packagist API (generally) contains more
 * information about a package than Composer offers directly, thus we use it to augment the information retrieved from
 * Composer.
 *
 * @author Ben Thomson <git@alfreido.com>
 * @since 0.3.0
 */
class Packagist
{
    protected const PACKAGIST_API_URL = 'https://packagist.org/';
    protected const PACKAGIST_REPO_URL = 'https://repo.packagist.org/p2/';

    protected static string $agent = 'Winter Packager <no-reply@example.com>';

    protected static ?Storage $storage = null;

    /**
     * Get information on a package in the Packagist API.
     *
     * @return array<string, mixed>
     */
    public static function getPackage(
        string $namespace,
        string $name,
        ?string $version = null
    ): array {
        if (!is_null(static::$storage) && static::$storage->has($namespace, $name, $version)) {
            return static::$storage->get($namespace, $name, $version);
        }

        $client = static::getClient();
        $request = static::newRepoRequest($namespace . '/' . $name . '.json');

        $response = $client->sendRequest($request);

        if ($response->getStatusCode() === 404) {
            throw new PackagistException('Package not found');
        }

        if ($response->getStatusCode() !== 200) {
            throw new PackagistException('Failed to retrieve package information');
        }

        $body = json_decode($response->getBody()->getContents(), true);

        if (!isset($body['packages'][$namespace . '/' . $name])) {
            throw new PackagistException('Package information not found');
        }

        $versions = [];
        foreach (MetadataMinifier::expand($body['packages'][$namespace . '/' . $name]) as $packageVersion) {
            $versions[$packageVersion['version_normalized']] = $packageVersion;

            // Store metadata
            if (!is_null(static::$storage)) {
                static::$storage->set($namespace, $name, $packageVersion['version_normalized'], $packageVersion);
            }
        }

        if (is_null($version)) {
            return $versions[array_keys($versions)[0]];
        }

        $parser = new VersionParser;
        $versionNormalized = $parser->normalize($version);

        if (!array_key_exists($versionNormalized, $versions)) {
            throw new PackagistException('Package version not found');
        }

        return $versions[$versionNormalized];
    }

    /**
     * Retrieves a list of package names (and types) from the Packagist API.
     *
     * @param \Winter\Packager\Enums\ListType $type
     * @param null|string $query
     * @return array<string, array<string, array<string, string>>>
     */
    public static function listPackages(
        ListType $type = ListType::ALL,
        ?string $query = null
    ) {
        $client = static::getClient();

        switch ($type) {
            case ListType::ALL:
                $request = static::newApiRequest('packages/list.json?fields[]=type');
                break;
            case ListType::NAMESPACE:
                if (empty($query)) {
                    throw new PackagistException('Namespace query cannot be empty');
                }
                $request = static::newApiRequest('packages/list.json?fields[]=type&vendor=' . urlencode($query));
                break;
            case ListType::TYPE:
                if (empty($query)) {
                    throw new PackagistException('Type query cannot be empty');
                }
                $request = static::newApiRequest('packages/list.json?fields[]=type&type=' . urlencode($query));
                break;
        }

        $response = $client->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new PackagistException('Failed to retrieve package list');
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public static function getClient(): ClientInterface
    {
        return Psr18ClientDiscovery::find();
    }

    /**
     * Set the user agent for the Packagist API requests.
     *
     * To comply with Packagist's requirements for use of their API, we require that agent names contain a name or
     * reference to the system being used, and a contact email address in the format of:
     *
     * `Name or Reference <email@address.com>`
     */
    public static function setAgent(string $agent): void
    {
        if (!preg_match('/^(.+) <(.+)>$/', $agent, $matches)) {
            throw new \InvalidArgumentException(
                'Agent must be in the format of `Name or Reference <email@address.com>`'
            );
        }

        [$name, $email] = $matches;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Agent email address is not valid');
        }

        static::$agent = trim($name) . ' <' . trim($email) . '>';
    }

    /**
     * Sets the storage for metadata.
     */
    public static function setStorage(?Storage $storage = null): void
    {
        static::$storage = $storage;
    }

    public static function newApiRequest(string $url = ''): RequestInterface
    {
        $request = Psr17FactoryDiscovery::findRequestFactory()->createRequest('GET', self::PACKAGIST_API_URL . ltrim($url, '/'));
        $request->withHeader('Accept', 'application/json');
        $request->withHeader('Content-Type', 'application/json');
        $request->withHeader('User-Agent', static::$agent);

        return $request;
    }

    public static function newRepoRequest(string $url = ''): RequestInterface
    {
        $request = Psr17FactoryDiscovery::findRequestFactory()->createRequest('GET', self::PACKAGIST_REPO_URL . ltrim($url, '/'));
        $request->withHeader('Accept', 'application/json');
        $request->withHeader('Content-Type', 'application/json');
        $request->withHeader('User-Agent', static::$agent);

        return $request;
    }
}
