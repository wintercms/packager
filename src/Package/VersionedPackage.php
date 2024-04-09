<?php

namespace Winter\Packager\Package;

use Composer\Semver\VersionParser;
use Winter\Packager\Enums\VersionStatus;

class VersionedPackage extends Package
{
    protected string $versionNormalized;
    protected string $latestVersionNormalized;

    public function __construct(
        string $namespace,
        string $name,
        string $description = '',
        protected string $version = '',
        protected string $latestVersion = '',
        protected VersionStatus $updateStatus = VersionStatus::UP_TO_DATE,
    ) {
        parent::__construct($namespace, $name, $description);

        $this->versionNormalized = $this->normalizeVersion($version);
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
}
