<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\Composer\DependencyInstaller;

use Composer\Command\ConfigCommand;
use Composer\Command\RequireCommand;
use Composer\Console\Application;
use Composer\Factory;
use Composer\Json\JsonFile;
use Symfony\Component\Console\Input\ArrayInput;

class DependencyInstaller
{
    /**
     * @var array
     */
    private $composerDefinition;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $composerFile             = Factory::getComposerFile();
        $composerJson             = new JsonFile($composerFile);
        $this->composerDefinition = $composerJson->read();
    }
    /**
     * Install a repository.
     *
     * @param string $name
     * @param string $type
     * @param string $url
     *
     * @return void
     */
    public function installRepository(string $name, string $type, string $url)
    {
        if (array_key_exists(
            $name,
            $this->composerDefinition['repositories']
        )) {
            return;
        }

        $application = new Application();
        $command     = new ConfigCommand();

        $definition = clone $application->getDefinition();
        $definition->addArguments($command->getDefinition()->getArguments());
        $definition->addOptions($command->getDefinition()->getOptions());

        $input = new ArrayInput(
            [
                'command' => 'config',
                'setting-key' => 'repositories.' . $name,
                'setting-value' => [
                    $type,
                    $url
                ]
            ],
            $definition
        );

        //$configCommand->setComposer($this->composer);
        //$configCommand->setApplication($application);
        //$configCommand->setIO($this->io);
        $application->run($input);
    }

    /**
     * Install a composer package.
     *
     * @param string $package
     *
     * @return void
     */
    public function installPackage(string $package)
    {
        if (in_array($package, $this->composerDefinition['require-dev'])) {
            return;
        }

        $application = new Application();
        $command     = new RequireCommand();

        $definition = clone $application->getDefinition();
        $definition->addArguments($command->getDefinition()->getArguments());
        $definition->addOptions($command->getDefinition()->getOptions());

        $input = new ArrayInput(
            [
                'command' => 'require',
                'packages' => [$package . ':@stable'],
                '--dev' => true,
                '--no-scripts' => true,
                '--no-interaction' => true,
                '--no-plugins' => true,
            ],
            $definition
        );

        //$requireCommand->setComposer($this->composer);
        //$requireCommand->setApplication($application);
        //$requireCommand->setIO($this->io);
        $application->run($input);
    }
}
