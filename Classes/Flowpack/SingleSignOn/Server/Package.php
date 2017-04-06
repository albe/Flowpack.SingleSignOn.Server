<?php
namespace Flowpack\SingleSignOn\Server;

/*                                                                               *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Server". *
 *                                                                               */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;

/**
 * Connect SSO specific signals
 */
class Package extends \Neos\Flow\Package\Package {

	/**
	 * @param Bootstrap $bootstrap
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		$bootstrap->getSignalSlotDispatcher()->connect(
			'Neos\Flow\Security\Authentication\AuthenticationProviderManager',
			'loggedOut',
			'Flowpack\SingleSignOn\Server\Service\AccountManager',
			'destroyRegisteredClientSessions'
		);
	}
}

