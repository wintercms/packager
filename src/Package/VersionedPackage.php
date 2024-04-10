<?php

namespace Winter\Packager\Package;

use Composer\Semver\VersionParser;
use Winter\Packager\Composer;
use Winter\Packager\Enums\VersionStatus;

/**
 * @author Ben Thomson <git@alfreido.com>
 * @since 0.3.0
 */
class VersionedPackage extends Package
{
    protected string $versionNormalized;
    protected string $latestVersionNormalized;

    public function __construct(
        string $namespace,
        string $name,
        string $description = '',
        string $type = '',
        protected string $version = '',
        protected string $latestVersion = '',
        protected VersionStatus $updateStatus = VersionStatus::UP_TO_DATE,
    ) {
        parent::__construct($namespace, $name, $description, $type);

        $this->versionNormalized = $this->normalizeVersion($this->version);
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
        $this->versionNormalized = $this->normalizeVersion($version);
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getVersionNormalized(): string
    {
        return $this->versionNormalized;
    }

    public function setLatestVersion(string $latestVersion): void
    {
        $this->latestVersion = $latestVersion;
        $this->latestVersionNormalized = $this->normalizeVersion($latestVersion);
    }

    public function getLatestVersion(): string
    {
        return $this->latestVersion;
    }

    public function getLatestVersionNormalized(): string
    {
        return $this->latestVersionNormalized;
    }

    public function setUpdateStatus(VersionStatus $updateStatus): void
    {
        $this->updateStatus = $updateStatus;
    }

    public function getUpdateStatus(): VersionStatus
    {
        return $this->updateStatus;
    }

    protected function normalizeVersion(string $version): string
    {
        $parser = new VersionParser;
        return $parser->normalize($version);
    }

    public function toDetailed(): DetailedVersionedPackage
    {
        $details = Packagist::getPackage($this->namespace, $this->name, $this->version);

        return Composer::newDetailedVersionedPackage(
            namespace: $this->namespace,
            name: $this->name,
            description: $this->description ?? '',
            type: $details['type'] ?? 'library',
            keywords: $details['keywords'] ?? [],
            homepage: $details['homepage'] ?? '',
            authors: $details['authors'] ?? [],
            licenses: $details['licenses'] ?? [],
            support: $details['support'] ?? [],
            funding: $details['funding'] ?? [],
            requires: $details['require'] ?? [],
            devRequires: $details['require-dev'] ?? [],
            extras: $details['extra'] ?? [],
            suggests: $details['suggest'] ?? [],
            conflicts: $details['conflict'] ?? [],
            replaces: $details['replace'] ?? [],
            readme: $details['readme'] ?? '',
            version: $details['version'],
        );
    }
}
