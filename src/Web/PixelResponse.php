<?php
namespace GMO\Common\Web;

use Symfony\Component\HttpFoundation\Response;

/**
 * A transparent pixel response
 *
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Response\PixelResponse} instead.
 */
class PixelResponse extends Response {

	/**
	 * Base64 encoded transparent pixel
	 */
	const PIXEL = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

	public function __construct() {
		parent::__construct(null);

		$this->content = base64_decode(static::PIXEL);
		$this->headers->set('Content-Type', 'image/gif');
	}

	public function setContent($content) {
		if ($content !== null) {
			throw new \LogicException('The content cannot be set on a PixelResponse instance.');
		}
	}
}
