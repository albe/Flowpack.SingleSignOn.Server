<?php
namespace Flowpack\SingleSignOn\Server\Aspect;

/*                                                                               *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Server". *
 *                                                                               */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;

/**
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class SynchronizeClientSessionsAspect {

	/**
	 * @Flow\Inject
	 * @var \Flowpack\SingleSignOn\Server\Session\SsoSessionManager
	 */
	protected $singleSignOnSessionManager;

	/**
	 * Destroys client sessions if the server session gets a new id
	 *
	 * @Flow\Before("within(Neos\Flow\Session\SessionInterface) && method(.*->renewId())")
	 * @param JoinPointInterface $joinPoint The current joinpoint
	 */
	public function destroyClientSessionsOnRenewId(JoinPointInterface $joinPoint) {
		$session = $joinPoint->getProxy();
		$this->singleSignOnSessionManager->destroyRegisteredSsoClientSessions($session);
	}

}

