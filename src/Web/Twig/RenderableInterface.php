<?php
namespace GMO\Common\Web\Twig;

interface RenderableInterface {

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
