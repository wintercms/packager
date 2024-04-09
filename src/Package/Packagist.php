<?php

namespace Winter\Packager\Package;

use Composer\MetadataMinifier\MetadataMinifier;
use Composer\Semver\VersionParser;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Winter\Packager\Exceptions\PackagistException;

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

    /**
     * Get information on a package in the Packagist API.
     *
     * @return array<string, mixed>
     */
    public static function getPackage(string $namespace, string $name, ?string $version = null): array
    {
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

        if (is_null($version)) {
            if (!isset($body['packages'][$namespace . '/' . $name][0])) {
                throw new PackagistException('Package information not found');
            }
        } else {
            if (!isset($body['packages'][$namespace . '/' . $name])) {
                throw new PackagistException('Package information not found');
            }

            $versions = MetadataMinifier::expand($body['packages'][$namespace . '/' . $name]);
            $parser = new VersionParser;
            $packageVersionNormalized = $parser->normalize($version);

            foreach ($versions as $packageVersion) {
                if ($packageVersion['version_normalized'] === $packageVersionNormalized) {
                    return $packageVersion;
                }
            }

            throw new PackagistException('Package version not found');
        }

        return $body['packages'][$namespace . '/' . $name][0];
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
