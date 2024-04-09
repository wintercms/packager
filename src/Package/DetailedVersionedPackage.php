<?php

namespace Winter\Packager\Package;

use Composer\Package\Version\VersionParser;
use Winter\Packager\Enums\VersionStatus;

/**
 * @author Ben Thomson <git@alfreido.com>
 * @since 0.3.0
 */
class DetailedVersionedPackage extends Package
{
    protected string $versionNormalized;
    protected string $latestVersionNormalized;

    /**
     * Constructor.
     *
     * @param array<int, string> $keywords
     * @param array<int, array<string, string>> $authors
     * @param array<int, array<string, string>> $licenses
     * @param array<string, string> $support
     * @param array<string, string> $funding
     * @param array<string, string> $requires
     * @param array<string, string> $devRequires
     * @param array<string, mixed> $extras
     * @param array<string, string> $suggests
     * @param array<string, string> $conflicts
     * @param array<string, string> $replaces
     */
    public function __construct(
        string $namespace,
        string $name,
        string $description = '',
        protected string $type = 'library',
        protected array $keywords = [],
        protected string $homepage = '',
        protected array $authors = [],
        protected array $licenses = [],
        protected array $support = [],
        protected array $funding = [],
        protected array $requires = [],
        protected array $devRequires = [],
        protected array $extras = [],
        protected array $suggests = [],
        protected array $conflicts = [],
        protected array $replaces = [],
        protected string $readme = '',
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
