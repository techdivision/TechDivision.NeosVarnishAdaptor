<?php
namespace TechDivision\NeosVarnishAdaptor\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TechDivision.NeosVarnishAdaptor".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Http\HttpRequestHandlerInterface;
use TYPO3\Flow\Http\Response;
use TYPO3\Flow\Mvc\Controller\ControllerInterface;
use TYPO3\Flow\Mvc\RequestInterface;
use TYPO3\Flow\Mvc\ResponseInterface;
use TYPO3\Neos\Controller\Frontend\NodeController;
use TYPO3\Neos\Domain\Service\ContentContext;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * Service for adding cache headers to a to-be-sent response
 *
 * @Flow\Scope("singleton")
 */
class CacheControlService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Core\Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @Flow\Inject
	 * @var SitemapExportService
	 */
	protected $sitemapExportService;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Adds cache headers to the response
	 *
	 * Called via a signal triggered by the MVC Dispatcher
	 *
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @param ControllerInterface $controller
	 * @return void
	 */
	public function addHeaders(RequestInterface $request, ResponseInterface $response, ControllerInterface $controller) {
		if ($response instanceof Response && $controller instanceof NodeController) {
			$arguments = $controller->getControllerContext()->getArguments();
			if ($arguments->hasArgument('node')) {
				$node = $arguments->getArgument('node')->getValue();
				if ($node instanceof NodeInterface) {
					$response->setHeader('X-Neos-Powered', 'Neos/dev-way-beyond-master');
					$response->setHeader('X-Neos-SecretSpeedMode', 'lightspeed');
				}
			}
		}
	}

	/**
	 * Sets a flag in the file system which signals an external spider that the page cache
	 * needs to be updated.
	 *
	 * @param $node
	 * @return void
	 */
	public function triggerRecaching($node) {
		if (!file_exists($this->settings['spiderIndexingPathAndFilename']) && $node instanceof NodeInterface) {
			file_put_contents($this->settings['spiderIndexingPathAndFilename'], time());
		}
	}

}
