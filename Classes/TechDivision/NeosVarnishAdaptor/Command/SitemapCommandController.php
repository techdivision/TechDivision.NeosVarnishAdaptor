<?php
namespace TechDivision\NeosVarnishAdaptor\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TechDivision.NeosVarnishAdaptor".*
 *                                                                        *
 *                                                                        */

use TechDivision\NeosVarnishAdaptor\Service\SitemapExportService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Neos\Domain\Repository\SiteRepository;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;

/**
 * @Flow\Scope("singleton")
 */
class SitemapCommandController extends CommandController {

	/**
	 * @Flow\Inject
	 * @var SiteRepository
	 */
	protected $siteRepository;

	/**
	 * @Flow\Inject
	 * @var ContextFactoryInterface
	 */
	protected $contextFactory;

	/**
	 * @Flow\Inject
	 * @var SitemapExportService
	 */
	protected $sitemapExportService;

	/**
	 * Exports the sitemap as XML
	 *
	 * This command creates a sitemaps.org schema compliant sitemap of a
	 * Neos website.
	 *
	 * Example:
	 *
	 * ./flow sitemap:export --exclude-paths-recursively tags
	 *   --base-uri http://example.com/ examplecom > Web/sitemap.xml
	 *
	 * @param string $site Node name of the site (e.g. "neostypo3org")
	 * @param string $baseUri The base URI which is prepended to all generated URIs (e.g. "http://example.com/")
	 * @param string $excludePathsRecursively A comma separated list of node paths to exclude from the sitemap, also excluding any children
	 * @return void
	 */
	public function exportCommand($site, $baseUri = 'http://localhost/', $excludePathsRecursively = '') {
		$contentContext = $this->contextFactory->create(array('workspaceName' => 'live', 'invisibleContentShown' => FALSE, 'inaccessibleContentShown' => FALSE));
		$siteObject = $this->siteRepository->findOneByNodeName($site);
		if ($siteObject === NULL) {
			$this->outputLine('Site %s does not exist.', array($site));
			$this->quit(1);
		}
		$this->output($this->sitemapExportService->exportSitemap($siteObject, $contentContext, $baseUri, Arrays::trimExplode(',', $excludePathsRecursively)));
	}

}

?>