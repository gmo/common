<?php

namespace GMO\Common\Web\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Implements a lazy UrlGenerator.
 * Similar concept with {@see \Silex\LazyUrlMatcher LazyUrlMatcher} and
 * {@see \Symfony\Component\HttpKernel\EventListener\RouterListener RouterListener}
 *
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Routing\LazyUrlGenerator} instead.
 */
class LazyUrlGenerator implements UrlGeneratorInterface
{
    private $factory;

    /**
     * Constructor.
     *
     * @param \Closure $factory
     */
    public function __construct(\Closure $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->getUrlGenerator()->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->getUrlGenerator()->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->getUrlGenerator()->generate($name, $parameters, $referenceType);
    }

    /**
     * @return UrlGeneratorInterface
     */
    public function getUrlGenerator()
    {
        $urlGenerator = call_user_func($this->factory);
        if (!$urlGenerator instanceof UrlGeneratorInterface) {
            throw new \LogicException(
                'Factory supplied to LazyUrlGenerator must return implementation of UrlGeneratorInterface.'
            );
        }

        return $urlGenerator;
    }
}
