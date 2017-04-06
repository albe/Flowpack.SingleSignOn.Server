<?php
namespace Flowpack\SingleSignOn\Server\Controller;

/*                                                                               *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Server". *
 *                                                                               */

use Flowpack\SingleSignOn\Server\Domain\Model\AccessToken;
use Neos\Flow\Annotations as Flow;
use Flowpack\SingleSignOn\Server\Exception;
use Neos\Flow\Mvc\Controller\ActionController;

/**
 * Access token management controller
 *
 * Acts as server-to-server REST service to redeem access tokens
 * into account data and the global session id.
 *
 * @Flow\Scope("singleton")
 */
class AccessTokenController extends ActionController {

	/**
	 * @Flow\Inject
	 * @var \Flowpack\SingleSignOn\Server\Domain\Repository\AccessTokenRepository
	 */
	protected $accessTokenRepository;

	/**
	 * @Flow\Inject
	 * @var \Flowpack\SingleSignOn\Server\Service\ClientAccountMapperInterface
	 */
	protected $clientAccountMapper;

	/**
	 * @Flow\Inject
	 * @var \Neos\Flow\Session\SessionManagerInterface
	 */
	protected $sessionManager;

	/**
	 * @Flow\Inject
	 * @var \Flowpack\SingleSignOn\Server\Session\SsoSessionManager
	 */
	protected $singleSignOnSessionManager;

	/**
	 * @Flow\Inject
	 * @var \Flowpack\SingleSignOn\Server\Log\SsoLoggerInterface
	 */
	protected $ssoLogger;

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'Neos\Flow\Mvc\View\JsonView';

	/**
	 * @var array
	 */
	protected $supportedMediaTypes = array('application/json');

	/**
	 * Redeem an access token and return global account data for the authenticated account
	 * and a global session id.
	 *
	 * POST token/{accessToken}/redeem?clientSessionId=abc
	 *
	 * @param string $accessToken
	 */
	public function redeemAction($accessToken) {
		if ($this->request->getHttpRequest()->getMethod() !== 'POST') {
			$this->response->setStatus(405);
			$this->response->setHeader('Allow', 'POST');
			return;
		}

		$accessTokenObject = $this->accessTokenRepository->findByIdentifier($accessToken);
		if (!$accessTokenObject instanceof AccessToken) {
			$this->response->setStatus(404);
			$this->view->assign('value', array('message' => 'Invalid access token'));
			return;
		}

		$sessionId = $accessTokenObject->getSessionId();
		$session = $this->sessionManager->getSession($sessionId);
		if (!$this->sessionIsActive($session)) {
			$this->response->setStatus(403);
			$this->view->assign('value', array('message' => 'Session expired'));
			return;
		}

		$this->accessTokenRepository->remove($accessTokenObject);

		// TODO Move the actual logic of redemption to a service

		$ssoClient = $accessTokenObject->getSsoClient();
		$this->singleSignOnSessionManager->registerSsoClient($session, $ssoClient);

		// TODO Get the account from the global session
		// TODO What to do with multiple accounts?
		$account = $accessTokenObject->getAccount();
		$accountData = $this->clientAccountMapper->getAccountData($accessTokenObject->getSsoClient(), $account);

		if ($this->ssoLogger !== NULL) {
			$this->ssoLogger->log('Redeemed access token "' . $accessToken . '" from client "' . $ssoClient->getServiceBaseUri() . '" for session "' . $sessionId . '" and account "' . $account->getAccountIdentifier() . '"', LOG_INFO);
		}

		$this->response->setStatus(201);

		$this->view->assign('value', array(
			'account' => $accountData,
			'sessionId' => $sessionId
		));
	}

	/**
	 * Test if the given session is active and not expired
	 *
	 * @param \Neos\Flow\Session\SessionInterface $session
	 * @return boolean
	 */
	protected function sessionIsActive($session) {
		return $session !== NULL && $session->isStarted();
	}

}
