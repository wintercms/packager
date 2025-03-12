<?php

namespace Winter\Packager\Package;

use Winter\Packager\Composer;

/**
 * Base package class.
 *
 * At a bare minimum, a package has a namespace and a name. For example, for the package "wintercms/winter", the
 * namespace would be "wintercms" and the name would be "winter".
 *
 * The package may have a description, and may have a type.
 *
 * This is generally returned by searches and lists, as we may or may not have the full package information available
 * in the output of these commands. You can convert this to a detailed package by using the `Package::toDetailed()`
 * method, which will extract information about the package direct from Packagist API.
 *
 * @author Ben Thomson <git@alfreido.com>
 * @since 0.3.0
 */
class Package
{
    public function __construct(
        protected string $namespace,
        protected string $name,
        protected string $description = '',
        protected string $type = '',
        protected ?string $path = null,
    ) {
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPackageName(): string
    {
        return implode('/', [$this->namespace, $this->name]);
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function toDetailed(): DetailedPackage
    {
        $details = Packagist::getPackage($this->namespace, $this->name);

        return Composer::newDetailedPackage(
            namespace: $this->namespace,
            name: $this->name,
            description: $this->description ?? '',
            keywords: $details['keywords'] ?? [],
            type: $details['type'] ?? 'library',
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
            readme: $details['readme'] ?? ''
        );
    }
}
