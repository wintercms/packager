<?php

namespace Winter\Packager\Enums;

/**
 * Version status.
 *
 * This defines all applicable statuses for a package version when determining if a package is up to date or not.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
enum VersionStatus: string
{
    /**
     * Package is up to date and no updates are available.
     */
    case UP_TO_DATE = 'up-to-date';
    /**
     * Package has a newer version available that is compatible with your requirements.
     */
    case SEMVER_UPDATE = 'semver-safe-update';
    /**
     * Package has a newer version available that is not compatible with your requirements.
     */
    case MAJOR_UPDATE = 'update-possible';
}
