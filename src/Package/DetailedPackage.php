<?php

namespace Winter\Packager\Package;

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
    ) {
        parent::__construct($namespace, $name, $description);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

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

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function setAuthors(array $authors): void
    {
        $this->authors = $authors;
    }

    public function getLicenses(): array
    {
        return $this->licenses;
    }

    public function setLicenses(array $licenses): void
    {
        $this->licenses = $licenses;
    }

    public function getSupport(): array
    {
        return $this->support;
    }

    public function setSupport(array $support): void
    {
        $this->support = $support;
    }

    public function getFunding(): array
    {
        return $this->funding;
    }

    public function setFunding(array $funding): void
    {
        $this->funding = $funding;
    }

    public function getRequires(): array
    {
        return $this->requires;
    }

    public function setRequires(array $requires): void
    {
        $this->requires = $requires;
    }

    public function getDevRequires(): array
    {
        return $this->devRequires;
    }

    public function setDevRequires(array $devRequires): void
    {
        $this->devRequires = $devRequires;
    }

    public function getExtras(): array
    {
        return $this->extras;
    }

    public function setExtras(array $extras): void
    {
        $this->extras = $extras;
    }
}
