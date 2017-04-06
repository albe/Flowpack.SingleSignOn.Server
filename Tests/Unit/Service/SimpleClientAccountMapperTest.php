<?php
namespace Flowpack\SingleSignOn\Server\Tests\Unit\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Server".*
 *                                                                        *
 *                                                                        */

use \Neos\Flow\Http\Request;
use \Neos\Flow\Http\Response;
use \Neos\Flow\Http\Uri;

/**
 *
 */
class SimpleClientAccountMapperTest extends \Neos\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function getAccountDataMapsAccountInformation() {
		$ssoClient = new \Flowpack\SingleSignOn\Server\Domain\Model\SsoClient();
		$account = new \Neos\Flow\Security\Account();
		$account->setAccountIdentifier('jdoe');
		$account->setRoles(array(new \Neos\Flow\Security\Policy\Role('Administrator')));

		$mapper = new \Flowpack\SingleSignOn\Server\Service\SimpleClientAccountMapper();
		$data = $mapper->getAccountData($ssoClient, $account);

		$this->assertEquals(array(
			'accountIdentifier' => 'jdoe',
			'roles' => array('Administrator'),
			'party' => NULL
		), $data);
	}

	/**
	 * @test
	 */
	public function getAccountDataMapsPublicPartyProperties() {
		$ssoClient = new \Flowpack\SingleSignOn\Server\Domain\Model\SsoClient();
		$account = new \Neos\Flow\Security\Account();
		$account->setAccountIdentifier('jdoe');
		$account->setRoles(array(new \Neos\Flow\Security\Policy\Role('Administrator')));

		$party = new \Neos\Party\Domain\Model\Person();
		$party->setName(new \Neos\Party\Domain\Model\PersonName('', 'John', '', 'Doe'));
		$account->setParty($party);

		$mapper = new \Flowpack\SingleSignOn\Server\Service\SimpleClientAccountMapper();
		$data = $mapper->getAccountData($ssoClient, $account);

		$this->assertArrayHasKey('party', $data);
		$this->assertArrayHasKey('name', $data['party']);
		$this->assertArrayHasKey('firstName', $data['party']['name']);
		$this->assertEquals('John', $data['party']['name']['firstName']);
	}

	/**
	 * @test
	 */
	public function getAccountDataExposesTypeIfConfigured() {
		$ssoClient = new \Flowpack\SingleSignOn\Server\Domain\Model\SsoClient();
		$account = new \Neos\Flow\Security\Account();
		$account->setAccountIdentifier('jdoe');
		$account->setRoles(array(new \Neos\Flow\Security\Policy\Role('Administrator')));

		$party = new \Neos\Party\Domain\Model\Person();
		$party->setName(new \Neos\Party\Domain\Model\PersonName('', 'John', '', 'Doe'));
		$account->setParty($party);

		$mapper = new \Flowpack\SingleSignOn\Server\Service\SimpleClientAccountMapper();
		$mapper->setConfiguration(array(
			'party' => array('_exposeType' => TRUE)
		));
		$data = $mapper->getAccountData($ssoClient, $account);

		$this->assertArrayHasKey('party', $data);
		$this->assertArrayHasKey('__type', $data['party']);
		$this->assertEquals('Neos\Party\Domain\Model\Person', $data['party']['__type']);
	}

}

?>