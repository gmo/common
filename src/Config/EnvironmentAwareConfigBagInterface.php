<?php

namespace Gmo\Common\Config;

/**
 * A ConfigBagInterface that's also aware of the current environment name being used to pull values from.
 */
interface EnvironmentAwareConfigBagInterface extends ConfigBagInterface
{
    /**
     * Get the current environment name.
     *
     * @return string
     */
    public function getEnv();

    /**
     * Set the current environment name.
     *
     * @param string $env The environment name
     */
    public function setEnv($env);

    /**
     * Return a new ConfigBag using the given environment name.
     *
     * This doesn't modify the environment for this instance.
     *
     * @param string $env The environment name
     *
     * @return static
     */
    public function withEnv($env);
}
