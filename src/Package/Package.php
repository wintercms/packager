<?php

namespace Winter\Packager\Package;

/**
 * Base package class.
 *
 * At a bare minimum, a package has a namespace and a name. For example, for the package "wintercms/winter", the
 * namespace would be "wintercms" and the name would be "winter".
 *
 * The package may have a description.
 *
 * This is generally returned by searches and lists, as we may or may not have the full package information available
 * in the output of these commands. You can convert this to a detailed package by using the
 * `Package::getDetailedPackage()` method.
 *
 * @author Ben Thomson <git@alfreido.com>
 * @since 0.3.0
 */
class Package
{
    public function __construct(
        protected string $namespace,
        protected string $name,
        protected string $description = ''
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
}
