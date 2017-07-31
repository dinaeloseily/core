<?php
declare(strict_types=1);

namespace PhpList\PhpList4\Core;

use PhpList\PhpList4\ApplicationBundle\PhpListApplicationBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\WebServerBundle\WebServerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * This class takes care of processing HTTP requests using Symfony.
 *
 * @author Oliver Klee <oliver@phplist.com>
 */
class ApplicationKernel extends Kernel
{
    /**
     * @var ApplicationStructure
     */
    private $applicationStructure = null;

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        $bundles = [
            new FrameworkBundle(),
            new PhpListApplicationBundle(),
        ];

        if ($this->shouldHaveDevelopmentBundles()) {
            $bundles[] = new WebServerBundle();
        }

        // This will later be changed so that the REST API package can register itself to the core.
        if ($this->isRestBundleInstalled()) {
            $className = $this->getRestBundleClassName();
            $bundles[] = new $className();
        }

        return $bundles;
    }

    /**
     * Returns the directory of the project/application.
     *
     * @return string absolute path without the trailing slash
     */
    public function getProjectDir(): string
    {
        return $this->getAndCreateApplicationStructure()->getCorePackageRoot();
    }

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->getProjectDir();
    }

    /**
     * @return string
     */
    private function getApplicationDir(): string
    {
        return $this->getAndCreateApplicationStructure()->getApplicationRoot();
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->getApplicationDir() . '/var/cache/' . $this->getEnvironment();
    }

    /**
     * @return string
     */
    public function getLogDir(): string
    {
        return $this->getApplicationDir() . '/var/logs';
    }

    /**
     * @return ApplicationStructure
     */
    private function getAndCreateApplicationStructure(): ApplicationStructure
    {
        if ($this->applicationStructure === null) {
            $this->applicationStructure = new ApplicationStructure();
        }

        return $this->applicationStructure;
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader
     *
     * @return void
     *
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/Configuration/config_' . $this->getEnvironment() . '.yml');
    }

    /**
     * @return bool
     */
    private function isRestBundleInstalled(): bool
    {
        return class_exists($this->getRestBundleClassName());
    }

    /**
     * @return string
     */
    private function getRestBundleClassName(): string
    {
        return 'PhpList\\RestBundle\\PhpListRestBundle';
    }

    /**
     * @return bool
     */
    private function shouldHaveDevelopmentBundles(): bool
    {
        return $this->environment !== Environment::PRODUCTION;
    }
}