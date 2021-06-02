<?php

namespace BennoThommo\Packager\Parser;

/**
 * Parses the version string from "composer --version".
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
class VersionParser implements Parser
{
    /**
     * @inheritDoc
     */
    public function parse(array $output)
    {
        if (preg_match(
            '/^Composer ([^ ]+) ([0-9]{4}\-[0-9]{2}\-[0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2})$/',
            trim(implode(PHP_EOL, $output)),
            $matches
        ) !== 1) {
            return false;
        }

        return [
            'version' => $matches[1],
            'date' => $matches[2],
            'time' => $matches[3]
        ];
    }
}
