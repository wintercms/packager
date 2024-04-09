<?php

namespace Winter\Packager\Enums;

enum VersionStatus: string
{
    case UP_TO_DATE = 'up-to-date';
    case SEMVER_UPDATE = 'semver-safe-update';
    case MAJOR_UPDATE = 'update-possible';
}
