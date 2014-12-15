<?php
namespace GMO\Common\Web\Twig;

use GMO\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Response;

class TwigResponse extends Response {

	public function setTemplate($template) {
		$this->template = $template;
		return $this;
	}

	public function getTemplate() {
		return $this->template;
	}

	public function getVariables() {
		return $this->variables;
	}

	public function setVariables($variables) {
		$this->variables = new ArrayCollection($variables);
		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @return $this
	 */
	public function addVariable($name, $value) {
		$this->variables->set($name, $value);
		return $this;
	}

	/**
	 * @param ArrayCollection|array $values
	 * @return $this
	 */
	public function addVariables($values) {
		$this->variables->replace($values);
		return $this;
	}

	/**
	 * Constructor.
	 *
	 * @param string $template  The name of the template
	 * @param ArrayCollection|array  $variables The variables for the template
	 * @param int    $status    The response status code
	 * @param array  $headers   An array of response headers
	 *
	 * @throws \InvalidArgumentException When the HTTP status code is not valid
	 *
	 * @api
	 */
	public function __construct($template, $variables = array(), $status = 200, $headers = array()) {
		$this->template = $template;
		$this->variables = new ArrayCollection($variables);
		parent::__construct('', $status, $headers);
	}

	protected $template;
	/** @var ArrayCollection */
	protected $variables;
}
