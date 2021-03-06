<?php
namespace TYPO3\TYPO3CR\SearchCommons;

/*                                                                                                  *
 * This script belongs to the TYPO3 Flow package "TYPO3.TYPO3CR.SearchCommons".                     *
 *                                                                                                  *
 * It is free software; you can redistribute it and/or modify it under                              *
 * the terms of the GNU Lesser General Public License, either version 3                             *
 *  of the License, or (at your option) any later version.                                          *
 *                                                                                                  *
 * The TYPO3 project - inspiring people to share!                                                   *
 *                                                                                                  */

use TYPO3\Flow\Core\Booting\Step;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The SearchCommons Package
 */
class Package extends BasePackage {

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param Bootstrap $bootstrap The current bootstrap
	 *
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$package = $this;
		$dispatcher->connect('TYPO3\Flow\Core\Booting\Sequence', 'afterInvokeStep', function(Step $step) use ($package, $bootstrap) {
			if ($step->getIdentifier() === 'typo3.flow:persistence') {
				$package->registerIndexingSlots($bootstrap);
			}
		});
	}

	/**
	 * Registers slots for signals in order to be able to index nodes
	 *
	 * @param Bootstrap $bootstrap
	 */
	public function registerIndexingSlots(Bootstrap $bootstrap) {
		$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'nodeAdded', 'TYPO3\TYPO3CR\SearchCommons\Indexer\NodeIndexingManager', 'indexNode');
		$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'nodeUpdated', 'TYPO3\TYPO3CR\SearchCommons\Indexer\NodeIndexingManager', 'indexNode');
		$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'nodeRemoved', 'TYPO3\TYPO3CR\SearchCommons\Indexer\NodeIndexingManager', 'removeNode');
		$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\Neos\Service\PublishingService', 'nodePublished', 'TYPO3\TYPO3CR\SearchCommons\Indexer\NodeIndexingManager', 'indexNode', FALSE);
		$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\Flow\Persistence\Doctrine\PersistenceManager', 'allObjectsPersisted', 'TYPO3\TYPO3CR\SearchCommons\Indexer\NodeIndexingManager', 'flushQueues');
	}
}

