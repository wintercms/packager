<?php

namespace Winter\Packager\Package;

/**
 * @author Ben Thomson <git@alfreido.com>
 * @since 0.3.0
 */
class DetailedPackage extends Package
{
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
        protected ?string $path = null,
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
    ) {
        parent::__construct($namespace, $name, $description, $type, $path);
    }

    /**
     * @return array<int, string>
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * @param array<int, string> $keywords
     */
    public function setKeywords(array $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function getHomepage(): string
    {
        return $this->homepage;
    }

    public function setHomepage(string $homepage): void
    {
        $this->homepage = $homepage;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * @param array<int, array<string, string>> $authors
     */
    public function setAuthors(array $authors): void
    {
        $this->authors = $authors;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getLicenses(): array
    {
        return $this->licenses;
    }

    /**
     * @param array<int, array<string, string>> $licenses
     */
    public function setLicenses(array $licenses): void
    {
        $this->licenses = $licenses;
    }

    /**
     * @return array<string, string>
     */
    public function getSupport(): array
    {
        return $this->support;
    }

    /**
     * @param array<string, string> $support
     */
    public function setSupport(array $support): void
    {
        $this->support = $support;
    }

    /**
     * @return array<string, string>
     */
    public function getFunding(): array
    {
        return $this->funding;
    }

    /**
     * @param array<string, string> $funding
     */
    public function setFunding(array $funding): void
    {
        $this->funding = $funding;
    }

    /**
     * @return array<string, string>
     */
    public function getRequires(): array
    {
        return $this->requires;
    }

    /**
     * @param array<string, string> $requires
     */
    public function setRequires(array $requires): void
    {
        $this->requires = $requires;
    }

    /**
     * @return array<string, string>
     */
    public function getDevRequires(): array
    {
        return $this->devRequires;
    }

    /**
     * @param array<string, string> $devRequires
     */
    public function setDevRequires(array $devRequires): void
    {
        $this->devRequires = $devRequires;
    }

    /**
     * @return array<string, mixed>
     */
    public function getExtras(): array
    {
        return $this->extras;
    }

    /**
     * @param array<string, mixed> $extras
     */
    public function setExtras(array $extras): void
    {
        $this->extras = $extras;
    }
}
