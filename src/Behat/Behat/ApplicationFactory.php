<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat;

use Behat\Behat\Autoloader\ServiceContainer\AutoloaderExtension;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Behat\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Behat\Gherkin\ServiceContainer\GherkinExtension;
use Behat\Behat\Hook\ServiceContainer\HookExtension;
use Behat\Behat\Output\ServiceContainer\Formatter\PrettyFormatterFactory;
use Behat\Behat\Output\ServiceContainer\Formatter\ProgressFormatterFactory;
use Behat\Behat\Snippet\ServiceContainer\SnippetExtension;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\Behat\Transformation\ServiceContainer\TransformationExtension;
use Behat\Behat\Translator\ServiceContainer\TranslatorExtension;
use Behat\Testwork\ApplicationFactory as BaseFactory;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Filesystem\ServiceContainer\FilesystemExtension;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Specification\ServiceContainer\SpecificationExtension;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;

/**
 * Defines the way behat is created.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ApplicationFactory extends BaseFactory
{
    const VERSION = '3.0-dev';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return 'behat';
    }

    /**
     * {@inheritdoc}
     */
    protected function getVersion()
    {
        return self::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultExtensions()
    {
        $processor = new ServiceProcessor();

        return array(
            // Testwork extensions
            new CliExtension($processor),
            new CallExtension($processor),
            new SuiteExtension($processor),
            new EnvironmentExtension($processor),
            new SpecificationExtension($processor),
            new FilesystemExtension(),
            new ExceptionExtension($processor),

            // Behat extensions
            new AutoloaderExtension(),
            new TranslatorExtension(),
            new GherkinExtension($processor),
            new ContextExtension($processor),
            new OutputExtension('pretty', $this->getDefaultFormatterFactories($processor), $processor),
            new SnippetExtension($processor),
            new DefinitionExtension($processor),
            new EventDispatcherExtension($processor),
            new HookExtension(),
            new TransformationExtension($processor),
            new TesterExtension($processor),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentVariableName()
    {
        return 'BEHAT_PARAMS';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigPath()
    {
        $cwd = rtrim(getcwd(), DIRECTORY_SEPARATOR);
        $paths = array_filter(
            array(
                $cwd . DIRECTORY_SEPARATOR . 'behat.yml',
                $cwd . DIRECTORY_SEPARATOR . 'behat.yml.dist',
                $cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'behat.yml',
                $cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'behat.yml.dist',
            ),
            'is_file'
        );

        if (count($paths)) {
            return current($paths);
        }

        return null;
    }

    /**
     * Returns default formatter factories.
     *
     * @param ServiceProcessor $processor
     *
     * @return FormatterFactory[]
     */
    private function getDefaultFormatterFactories(ServiceProcessor $processor)
    {
        return array(
            new PrettyFormatterFactory($processor),
            new ProgressFormatterFactory($processor),
        );
    }
}
