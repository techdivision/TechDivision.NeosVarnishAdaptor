<?php
namespace TechDivision\NeosVarnishAdaptor;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TechDivision.NeosVarnishAdaptor".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Core\Booting\Step;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;

/**
 * Package
 */
class Package extends BasePackage {

	/**
	 * @param Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$dispatcher->connect('TYPO3\Flow\Mvc\Dispatcher', 'afterControllerInvocation', 'TechDivision\NeosVarnishAdaptor\Service\CacheControlService', 'addHeaders');
		$dispatcher->connect('TYPO3\Neos\Service\PublishingService', 'nodePublished', 'TechDivision\NeosVarnishAdaptor\Service\CacheControlService', 'triggerRecaching');
	}
}
