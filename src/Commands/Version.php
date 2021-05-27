<?php namespace BennoThommo\Packager\Commands;

use Symfony\Component\Console\Input\ArrayInput;

class Version implements Command
{
    public function execute(): bool
    {
        $output = $this->runCommand(
            new ArrayInput([
                '--version' => true
            ])
        );

        // Find version line
        foreach ($output as $line) {
            if (starts_with($line, 'Composer')) {
                preg_match('/Composer ([0-9\.]+)/i', $line, $matches);
                return $matches[1];
            }
        }

        throw new ApplicationException('Unable to determine installed Composer version');
    }

    protected function arguments(): ArrayInput
    {
        return new ArrayInput([]);
    }
}
