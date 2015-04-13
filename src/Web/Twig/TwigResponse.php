<?php
namespace GMO\Common\Web\Twig;

use GMO\Common\Collections\ArrayCollection;
use GMO\Common\String;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment as Environment;

class TwigResponse extends Response implements RenderableInterface {

	/**
	 * @param string|string[] $template
	 * @return $this
	 */
	public function setTemplate($template) {
		if ($this->rendered) {
			throw new \LogicException('Cannot set template after response is rendered');
		}
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
		if ($this->rendered) {
			throw new \LogicException('Cannot set variables after response is rendered');
		}
		$this->variables = new ArrayCollection($variables);
		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @return $this
	 */
	public function addVariable($name, $value) {
		if ($this->rendered) {
			throw new \LogicException('Cannot add variables after response is rendered');
		}
		$this->variables->set($name, $value);
		return $this;
	}

	/**
	 * @param ArrayCollection|array $values
	 * @return $this
	 */
	public function addVariables($values) {
		if ($this->rendered) {
			throw new \LogicException('Cannot add variables after response is rendered');
		}
		$this->variables->replace($values);
		return $this;
	}

	public function setContent($content) {
		if (!$this->rendered) {
			throw new \LogicException('Should not set content before response is rendered');
		}
		parent::setContent($content);
		$this->rendered = true;
		return $this;
	}

	public function getContent() {
		if (!$this->rendered) {
			throw new \LogicException('Should not get content before response is rendered');
		}
		return parent::getContent();
	}

	public function render(Environment $twig) {
		$templates = ArrayCollection::create($this->template)
			->map(function ($template) {
				return String::endsWith($template, '.twig') ? $template : $template . '.twig';
			});
		$this->setContent($twig->resolveTemplate($templates->toArray())->render($this->variables->toArray()));
	}

	public function isRendered() {
		return $this->rendered;
	}

	/**
	 * Constructor.
	 *
	 * @param string|string[]       $template  The name of the template(s)
	 * @param ArrayCollection|array $variables The variables for the template
	 * @param int                   $status    The response status code
	 * @param array                 $headers   An array of response headers
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
	/** @var bool */
	protected $rendered = false;
}
