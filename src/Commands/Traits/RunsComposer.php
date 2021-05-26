<?php namespace BennoThommo\Packager\Commands\Traits;

trait RunsComposer
{
    /**
     * Runs a Composer command programatically.
     *
     * Returns the output as an array of output lines.
     *
     * @param ArrayInput $input The command for the Composer app.
     * @return array
     */
    protected function runCommand(ArrayInput $input)
    {
        if (is_null($this->homeDir)) {
            throw new ApplicationException('No Composer home path specified');
        }
        if (is_null($this->workingDir)) {
            throw new ApplicationException('No working directory specified');
        }

        // Set memory limit to 1.5GB as per Composer's recommendations
        // (https://getcomposer.org/doc/articles/troubleshooting.md#memory-limit-errors)
        ini_set('memory_limit', '1.5G');

        // Swap out environment variables
        $composerHome = getenv('COMPOSER_HOME') ?: null;
        $interactive = getenv('COMPOSER_NO_INTERACTION') ?: null;
        $debugWarn = getenv('COMPOSER_DISABLE_XDEBUG_WARN') ?: null;
        putenv('COMPOSER_HOME=' . $this->homeDir);
        putenv('COMPOSER_NO_INTERACTION=1');
        putenv('COMPOSER_DISABLE_XDEBUG_WARN=1');

        // Set up Composer application
        $app = new Application();
        $app->setAutoExit(false);
        $app->setCatchExceptions(false);

        $output = new BufferedOutput();

        // Run Composer command
        $code = $app->run($input, $output);

        // Restore environment variables
        if (!is_null($composerHome)) {
            putenv('COMPOSER_HOME=' . $composerHome);
        }
        if (!is_null($interactive)) {
            putenv('COMPOSER_NO_INTERACTION=' . $interactive);
        }
        if (!is_null($debugWarn)) {
            putenv('COMPOSER_DISABLE_XDEBUG_WARN=' . $debugWarn);
        }

        return explode(PHP_EOL, trim($output->fetch()));
    }
}