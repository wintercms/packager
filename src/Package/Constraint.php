<?php

namespace Winter\Packager\Package;

use Winter\Packager\Exceptions\ConstraintException;

/**
 * A simple constraint builder.
 *
 * Allows for the creation of a package, PHP or extension constraint, which can be used for requirements or filters.
 *
 * @author Ben Thomson <git@alfreido.com>
 * @since 0.3.0
 */
class Constraint
{
    /**
     * The name of the package or system extension.
     */
    protected string $package;

    /**
     * @var array<int, array<string, string>> The version constraints, in array form.
     */
    protected array $constraints = [];

    /**
     * The version alias for the package.
     */
    protected string $alias = '';

    /**
     * Constructor.
     *
     * @throws \Winter\Packager\Exceptions\ConstraintException If package name is invalid
     */
    final public function __construct(?string $package = null)
    {
        if (!is_null($package)) {
            $this->setPackage($package);
        }
    }

    /**
     * Statically create a new constraint with the given package.
     *
     * @return static
     */
    public static function package(string $package)
    {
        return new static($package);
    }

    /**
     * Statically create a new constraint for PHP.
     *
     * @return static
     */
    public static function php()
    {
        return new static('php');
    }

    /**
     * Statically create a new constraint for a PHP extension.
     *
     * @return static
     */
    public static function extension(string $extension)
    {
        return new static('ext-' . $extension);
    }

    /**
     * Gets the package name for this constraint.
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * Sets the package name for this constraint.
     *
     * @throws \Winter\Packager\Exceptions\ConstraintException If package name is invalid
     */
    public function setPackage(string $package): void
    {
        if (
            $package !== 'php'
            && !preg_match('/^ext-[a-z0-9\-_]+$/', $package)
            && !preg_match('/^[a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*$/', $package)
        ) {
            throw new ConstraintException('Invalid package name provided for constraint');
        }

        $this->package = $package;
    }

    /**
     * Resets and defines an initial version constraint for the package.
     *
     * @return static
     */
    public function version(string $operator, ?string $version = null)
    {
        $this->constraints = [];
        $this->addConstraint('', $operator, $version);

        return $this;
    }

    /**
     * Defines a version constraint to be considered with the previous constraints.
     *
     * @return static
     */
    public function andVersion(string $operator, ?string $version = null)
    {
        $this->addConstraint(' ', $operator, $version);

        return $this;
    }

    /**
     * Defines a version constraint to be considered irrespective of previous constraints.
     *
     * @return static
     */
    public function orVersion(string $operator, ?string $version = null)
    {
        $this->addConstraint(' || ', $operator, $version);

        return $this;
    }

    /**
     * Defines a version alias for the entire constraint.
     *
     * @return static
     */
    public function alias(string $alias)
    {
        $this->alias = $this->validateVersion($alias);

        return $this;
    }

    /**
     * Adds a version constraint to the list.
     */
    protected function addConstraint(
        string $join = '',
        string $operator = '=',
        ?string $version = null,
    ): void {
        try {
            if (is_null($version) && $this->validateVersion($operator)) {
                $this->constraints[] = [
                    'join' => $join,
                    'operator' => '=',
                    'version' => $operator,
                ];
                return;
            }
        } catch (ConstraintException $e) {
            // Continue below
        }

        if (str_starts_with($version ?? $operator, '~') && $this->validateVersion(substr($version ?? $operator, 1))) {
            $this->constraints[] = [
                'join' => $join,
                'operator' => '=',
                'version' => $version ?? $operator,
            ];
            return;
        }

        if (str_starts_with($version ?? $operator, '^') && $this->validateVersion(substr($version ?? $operator, 1))) {
            $this->constraints[] = [
                'join' => $join,
                'operator' => '=',
                'version' => $version ?? $operator,
            ];
            return;
        }

        if (str_ends_with($version ?? $operator, '*') && $this->validateVersion(substr($version ?? $operator, 0, -2))) {
            $this->constraints[] = [
                'join' => $join,
                'operator' => '=',
                'version' => $version ?? $operator,
            ];
            return;
        }

        $this->constraints[] = [
            'join' => $join,
            'operator' => $this->validateOperator($operator),
            'version' => $this->validateVersion($version),
        ];
    }

    /**
     * Converts the package and constraint into a string for Composer usage.
     *
     * @return string
     */
    public function toString(): string
    {
        $string = '"' . $this->package . ' ';

        if (!count($this->constraints)) {
            $string .= '*"';
            return $string;
        }

        foreach ($this->constraints as $constraint) {
            $string .= $constraint['join'];

            if ($constraint['operator'] !== '=') {
                $string .= $constraint['operator'];
            }

            $string .= $constraint['version'];
        }

        $string = rtrim($string, ' ');

        if ($this->alias !== '') {
            $string .= ' as ' . $constraint['alias'];
        }

        return $string . '"';
    }

    /**
     * Validates the given version.
     *
     * This will return the version if it is valid, otherwise an exception will be thrown.
     *
     * @throws \Winter\Packager\Exceptions\ConstraintException
     */
    protected function validateVersion(string $version): string
    {
        if (preg_match('/^([0-9]+\.){0,2}[0-9]+(-[a-z0-9]+)?$/i', $version)) {
            return $version;
        }

        throw new ConstraintException('Invalid version constraint provided for package');
    }

    /**
     * Validates the given operator.
     *
     * This will return the version if it is valid, otherwise an exception will be thrown.
     *
     * @throws \Winter\Packager\Exceptions\ConstraintException
     */
    protected function validateOperator(string $operator): string
    {
        if (in_array($operator, ['=', '>', '>=', '<', '<=', '!='])) {
            return $operator;
        }

        throw new ConstraintException('Invalid operator provided for version constraint');
    }
}
