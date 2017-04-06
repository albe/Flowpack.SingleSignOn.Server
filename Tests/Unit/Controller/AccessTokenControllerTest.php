<?php
namespace Flowpack\SingleSignOn\Server\Tests\Unit\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Server".*
 *                                                                        *
 *                                                                        */

use \Mockery as m;

/**
 * Unit test for AccessTokenController
 */
class AccessTokenControllerTest extends \Neos\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function redeemActionWithInvalidMethodRespondsWith405() {
		$controller = new \Flowpack\SingleSignOn\Server\Controller\AccessTokenController();

		$response = new \Neos\Flow\Http\Response();
		$this->inject($controller, 'response', $response);
		$mockRequest = m::mock('Neos\Flow\Mvc\ActionRequest', array(
			'getHttpRequest->getMethod' => 'GET'
		));
		$this->inject($controller, 'request', $mockRequest);
		$this->inject($controller, 'view', m::mock('Neos\Flow\Mvc\View\ViewInterface')->shouldIgnoreMissing());

		$controller->redeemAction('invalid-accesstoken');

		$this->assertEquals(405, $response->getStatusCode());
	}

	/**
	 * @test
	 */
	public function redeemActionWithInvalidAccessTokenRespondsWith404() {
		$controller = new \Flowpack\SingleSignOn\Server\Controller\AccessTokenController();

		$response = new \Neos\Flow\Http\Response();
		$this->inject($controller, 'response', $response);
		$mockRequest = m::mock('Neos\Flow\Mvc\ActionRequest', array(
			'getHttpRequest->getMethod' => 'POST'
		));
		$this->inject($controller, 'request', $mockRequest);
		$mockAccessTokenRepository = m::mock('Flowpack\SingleSignOn\Server\Domain\Repository\AccessTokenRepository', array(
			'findByIdentifier' => NULL
		));
		$this->inject($controller, 'accessTokenRepository', $mockAccessTokenRepository);
		$this->inject($controller, 'view', m::mock('Neos\Flow\Mvc\View\ViewInterface')->shouldIgnoreMissing());

		$controller->redeemAction('invalid-accesstoken');

		$this->assertEquals(404, $response->getStatusCode());
	}

	/**
	 * @test
	 */
	public function redeemActionWithValidAccessTokenRemovesAccessToken() {
		$controller = new \Flowpack\SingleSignOn\Server\Controller\AccessTokenController();

		$response = new \Neos\Flow\Http\Response();
		$this->inject($controller, 'response', $response);
		$mockRequest = m::mock('Neos\Flow\Mvc\ActionRequest', array(
			'getHttpRequest->getMethod' => 'POST'
		));
		$this->inject($controller, 'request', $mockRequest);
		$mockAccount = m::mock('Neos\Flow\Security\Account');
		$mockSsoClient = m::mock('Flowpack\SingleSignOn\Server\Domain\Model\SsoClient');
		$mockAccessToken = m::mock('Flowpack\SingleSignOn\Server\Domain\Model\AccessToken', array(
			'getSessionId' => 'test-sessionid',
			'getAccount' => $mockAccount,
			'getSsoClient' => $mockSsoClient
		));
		$mockAccessTokenRepository = m::mock('Flowpack\SingleSignOn\Server\Domain\Repository\AccessTokenRepository', array(
			'findByIdentifier' => $mockAccessToken
		));
		$this->inject($controller, 'accessTokenRepository', $mockAccessTokenRepository);
		$mockClientAccountMapper = m::mock('Flowpack\SingleSignOn\Server\Service\ClientAccountMapperInterface', array(
			'getAccountData' => array()
		));
		$this->inject($controller, 'clientAccountMapper', $mockClientAccountMapper);
		$mockSessionManager = m::mock('Neos\Flow\Session\SessionManagerInterface');
		$mockSession = m::mock('Neos\Flow\Session\SessionInterface', array(
			'isStarted' => TRUE
		));
		$mockSessionManager->shouldReceive('getSession')->with('test-sessionid')->andReturn($mockSession);
		$this->inject($controller, 'sessionManager', $mockSessionManager);
		$this->inject($controller, 'uriBuilder', m::mock('Neos\Flow\Mvc\Routing\UriBuilder')->shouldIgnoreMissing());
		$this->inject($controller, 'view', m::mock('Neos\Flow\Mvc\View\ViewInterface')->shouldIgnoreMissing());
		$mockSingleSignOnSessionManager= m::mock('Flowpack\SingleSignOn\Server\Session\SsoSessionManager', array(
			'registerSsoClient' => NULL
		));
		$this->inject($controller, 'singleSignOnSessionManager', $mockSingleSignOnSessionManager);

		$mockAccessTokenRepository->shouldReceive('remove')->with($mockAccessToken)->once();

		$controller->redeemAction('valid-accesstoken');
	}

	/**
	 * @test
	 */
	public function redeemActionWithValidAccessTokenMapsAccountData() {
		$controller = new \Flowpack\SingleSignOn\Server\Controller\AccessTokenController();

		$response = new \Neos\Flow\Http\Response();
		$this->inject($controller, 'response', $response);
		$mockRequest = m::mock('Neos\Flow\Mvc\ActionRequest', array(
			'getHttpRequest->getMethod' => 'POST'
		));
		$this->inject($controller, 'request', $mockRequest);
		$mockAccount = m::mock('Neos\Flow\Security\Account');
		$mockSsoClient = m::mock('Flowpack\SingleSignOn\Server\Domain\Model\SsoClient');
		$mockAccessToken = m::mock('Flowpack\SingleSignOn\Server\Domain\Model\AccessToken', array(
			'getSessionId' => 'test-sessionid',
			'getAccount' => $mockAccount,
			'getSsoClient' => $mockSsoClient
		));
		$mockAccessTokenRepository = m::mock('Flowpack\SingleSignOn\Server\Domain\Repository\AccessTokenRepository', array(
			'findByIdentifier' => $mockAccessToken
		))->shouldIgnoreMissing();
		$this->inject($controller, 'accessTokenRepository', $mockAccessTokenRepository);
		$accountData = array(
			'accountIdentifier' => 'test-account',
			'roles' => array('Administrator'),
			'party' => array('name' => 'John Doe')
		);
		$mockClientAccountMapper = m::mock('Flowpack\SingleSignOn\Server\Service\ClientAccountMapperInterface', array(
			'getAccountData' => $accountData
		));
		$this->inject($controller, 'clientAccountMapper', $mockClientAccountMapper);
		$mockSingleSignOnSessionManager= m::mock('Flowpack\SingleSignOn\Server\Session\SsoSessionManager', array(
			'registerSsoClient' => NULL
		));
		$this->inject($controller, 'singleSignOnSessionManager', $mockSingleSignOnSessionManager);
		$mockSessionManager = m::mock('Neos\Flow\Session\SessionManagerInterface');
		$mockSession = m::mock('Neos\Flow\Session\SessionInterface', array(
			'isStarted' => TRUE
		));
		$mockSessionManager->shouldReceive('getSession')->with('test-sessionid')->andReturn($mockSession);
		$this->inject($controller, 'sessionManager', $mockSessionManager);
		$this->inject($controller, 'uriBuilder', m::mock('Neos\Flow\Mvc\Routing\UriBuilder')->shouldIgnoreMissing());
		$mockView = m::mock('Neos\Flow\Mvc\View\ViewInterface');
		$this->inject($controller, 'view', $mockView);

		$mockView->shouldReceive('assign')->with('value',
			m::subset(array('account' => $accountData))
		)->once();

		$controller->redeemAction('valid-accesstoken');
	}

	/**
	 * @test
	 */
	public function redeemActionWithInactiveSessionRespondsWith403() {
		$controller = new \Flowpack\SingleSignOn\Server\Controller\AccessTokenController();

		$response = new \Neos\Flow\Http\Response();
		$this->inject($controller, 'response', $response);
		$mockRequest = m::mock('Neos\Flow\Mvc\ActionRequest', array(
			'getHttpRequest->getMethod' => 'POST'
		));
		$this->inject($controller, 'request', $mockRequest);
		$mockAccount = m::mock('Neos\Flow\Security\Account');
		$mockSsoClient = m::mock('Flowpack\SingleSignOn\Server\Domain\Model\SsoClient');
		$mockAccessToken = m::mock('Flowpack\SingleSignOn\Server\Domain\Model\AccessToken', array(
			'getSessionId' => 'invalid-sessionid',
			'getAccount' => $mockAccount,
			'getSsoClient' => $mockSsoClient
		));
		$mockAccessTokenRepository = m::mock('Flowpack\SingleSignOn\Server\Domain\Repository\AccessTokenRepository', array(
			'findByIdentifier' => $mockAccessToken
		));
		$this->inject($controller, 'accessTokenRepository', $mockAccessTokenRepository);
		$this->inject($controller, 'clientAccountMapper', m::mock('Flowpack\SingleSignOn\Server\Service\ClientAccountMapperInterface'));
		$mockSessionManager = m::mock('Neos\Flow\Session\SessionManagerInterface');
		$mockSessionManager->shouldReceive('getSession')->with('invalid-sessionid')->andReturn(NULL);
		$this->inject($controller, 'sessionManager', $mockSessionManager);
		$this->inject($controller, 'uriBuilder', m::mock('Neos\Flow\Mvc\Routing\UriBuilder')->shouldIgnoreMissing());
		$this->inject($controller, 'view', m::mock('Neos\Flow\Mvc\View\ViewInterface')->shouldIgnoreMissing());

		$controller->redeemAction('valid-accesstoken');

		$this->assertEquals(403, $response->getStatusCode());
	}

	/**
	 * Check for Mockery expectations
	 */
	public function tearDown() {
		m::close();
	}

}
?>