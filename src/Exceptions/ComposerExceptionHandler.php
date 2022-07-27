<?php namespace Winter\Packager\Exceptions;

use Composer\Plugin\PluginBlockedException;
use Winter\Packager\Commands\BaseCommand;

/**
 * Composer exception handler.
 *
 * Handles a Composer exception and returns a corresponding Packager exception.
 *
 * @author Ben Thomson
 * @since 0.2.0
 */
class ComposerExceptionHandler
{
    /**
     * Handles a Composer exception and returns a corresponding Packager exception.
     *
     * @param \Throwable $exception
     * @param BaseCommand $command
     * @return array<string, mixed>
     */
    public static function handle(\Throwable $exception, BaseCommand $command): array
    {
        if ($exception instanceof PluginBlockedException) {
            preg_match('/^([^ ]+)/', $exception->getMessage(), $matches);
            $plugin = $matches[1];

            return [
                'class' => ComposerJsonException::class,
                'message' => sprintf(
                    'The "%s" plugin has not been allowed in your composer.json file.',
                    $plugin
                )
            ];
        }

        // Default to throwing a Composer JSON exception
        return [
            'class' => ComposerJsonException::class,
            'message' => sprintf(
                'Your %s file is invalid.',
                $command->getComposer()->getConfigFile()
            ),
            'code' => 0,
            'previous' => $exception,
        ];
    }
}
