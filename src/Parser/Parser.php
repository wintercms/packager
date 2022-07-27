<?php

namespace Winter\Packager\Parser;

/**
 * Parser.
 *
 * A parser is a process that takes the output of a Composer command and parses it for the necessary information,
 * returning it to the Command.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
interface Parser
{
    /**
     * Parses the Composer output.
     *
     * The Composer output is expected to be an array of lines.
     *
     * @param string[] $output
     * @return mixed The parsed value.
     */
    public function parse(array $output);
}
