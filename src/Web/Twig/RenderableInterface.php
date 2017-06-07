<?php

namespace GMO\Common\Web\Twig;

/**
 * @deprecated since 1.30 will be removed in 2.0.
 */
interface RenderableInterface
{
    /**
     * Render the content with the given twig environment
     *
     * @param \Twig_Environment $twig
     */
    public function render(\Twig_Environment $twig);

    /**
     * Has the content been rendered?
     *
     * @return boolean
     */
    public function isRendered();
}
