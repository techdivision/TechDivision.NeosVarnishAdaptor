<?php
namespace TechDivision\NeosVarnishAdaptor\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TechDivision.NeosVarnishAdaptor".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
use TYPO3\Neos\Domain\Model\Site;
use TYPO3\Neos\Domain\Service\ContentContext;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;

/**
 * Service for generating sitemap XML according to sitemaps.org v0.9
 *
 * @Flow\Scope("singleton")
 */
class SitemapExportService {

	/**
	 * @Flow\Inject
	 * @var ContextFactoryInterface
	 */
	protected $contextFactory;

	/**
	 * @Flow\Inject
	 * @var ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Utility\Environment
	 */
	protected $environment;

	/**
	 * Absolute path to exported resources, or NULL if resources should be inlined in the exported XML
	 *
	 * @var string
	 */
	protected $resourcesPath = NULL;

	/**
	 * The XMLWriter that is used to construct the export.
	 *
	 * @var \XMLWriter
	 */
	protected $xmlWriter;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Mvc\Routing\RouterInterface
	 */
	protected $router;

	/**
	 * @Flow\Inject
	 * @var UriBuilder
	 */
	protected $uriBuilder;

	protected $baseUri;

	/**
	 * Fetches the site with the given name and exports its structure into XML
	 *
	 * @param Site $site
	 * @param ContentContext $contentContext
	 * @param string $baseUri
	 * @return string
	 */
	public function exportSitemap(Site $site, ContentContext $contentContext, $baseUri) {
		$this->initializeRouter();
		$this->baseUri = new Uri($baseUri);
		putenv('REDIRECT_FLOW_REWRITEURLS=1');

		$this->xmlWriter = new \XMLWriter();
		$this->xmlWriter->openMemory();
		$this->xmlWriter->setIndent(TRUE);
		$this->xmlWriter->startDocument('1.0', 'UTF-8');
		$this->xmlWriter->startElementNs(NULL, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		$this->exportSite($site, $contentContext);

		$this->xmlWriter->endElement();
		$this->xmlWriter->endDocument();
		return $this->xmlWriter->outputMemory(TRUE);
	}

	/**
	 * Export the given $site structure to the XMLWriter
	 *
	 * @param Site $site
	 * @param ContentContext $contentContext
	 * @return void
	 */
	protected function exportSite(Site $site, ContentContext $contentContext) {
		$contextProperties = $contentContext->getProperties();
		$contextProperties['currentSite'] = $site;
		$contentContext = $this->contextFactory->create($contextProperties);

		/** @var NodeInterface $siteNode */
		$siteNode = $contentContext->getCurrentSiteNode();
		$this->exportChildNodes($siteNode);
	}

	/**
	 * Export a single node to the XMLWriter
	 *
	 * @param NodeInterface $parentNode
	 * @return void
	 */
	protected function exportChildNodes(NodeInterface $parentNode) {
		foreach ($parentNode->getChildNodes('TYPO3.Neos:Page') as $childNode) {
			/** @var NodeInterface $childNode */
			$this->exportNode($childNode);
			if ($childNode->hasChildNodes()) {
				$this->exportChildNodes($childNode);
			}
		}
	}

	/**
	 * Export a single node to the XMLWriter
	 *
	 * @param NodeInterface $node
	 * @return void
	 */
	protected function exportNode(NodeInterface $node) {
		$this->xmlWriter->startElement('url');

		$this->uriBuilder->reset();
		$this->uriBuilder->setRequest(Request::create($this->baseUri)->createActionRequest());
		$this->uriBuilder->setFormat('html');
		$this->uriBuilder->setCreateAbsoluteUri(TRUE);
		$uri = $this->uriBuilder->uriFor('show', array('node' => $node), 'Frontend\Node', 'TYPO3.Neos');

		$this->xmlWriter->startElement('loc');
		$this->xmlWriter->text($uri);
		$this->xmlWriter->endElement();

		$this->xmlWriter->endElement();
	}

	/**
	 * Initialize the injected router-object
	 *
	 * @return void
	 */
	protected function initializeRouter() {
		$routesConfiguration = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_ROUTES);
		$this->router->setRoutesConfiguration($routesConfiguration);
	}

}
