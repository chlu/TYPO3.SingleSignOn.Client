<?php
namespace TYPO3\SingleSignOn\Client\Tests\Unit\Security\RequestPattern;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use \Mockery as m;
use \TYPO3\Flow\Http\Uri;

/**
 *
 */
class SignedRequestPatternTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function matchRequestWithRequestAndNoSignatureHeaderReturnsTrue() {
		$pattern = new \TYPO3\SingleSignOn\Client\Security\RequestPattern\SignedRequestPattern();

		$mockHttpRequest = m::mock('TYPO3\Flow\Http\Request');
		$mockActionRequest = m::mock('TYPO3\Flow\Mvc\ActionRequest', array(
			'getHttpRequest' => $mockHttpRequest
		));

		$mockHttpRequest->shouldReceive('hasHeader')->with('X-Request-Signature')->andReturn(FALSE);

		$result = $pattern->matchRequest($mockActionRequest);
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function matchRequestWithRequestAndValidSignatureHeaderVerifiesSignature() {
		$pattern = new \TYPO3\SingleSignOn\Client\Security\RequestPattern\SignedRequestPattern();

		$mockRsaWalletService = m::mock('TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface');
		$this->inject($pattern, 'rsaWalletService', $mockRsaWalletService);

		$mockPublicKeyResolver = m::mock('TYPO3\SingleSignOn\Client\Security\RequestPattern\PublicKeyResolverInterface');
		$this->inject($pattern, 'publicKeyResolver', $mockPublicKeyResolver);

		$mockHttpRequest = m::mock('TYPO3\Flow\Http\Request', array(
			'getMethod' => 'POST',
			'getContent' => 'Request content',
			'getUri' => new Uri('http://test/sso?foo=bar')
		));
		$mockActionRequest = m::mock('TYPO3\Flow\Mvc\ActionRequest', array(
			'getHttpRequest' => $mockHttpRequest
		));

		$mockPublicKeyResolver->shouldReceive('resolveFingerprintByIdentifier')->with('SomeIdentifier')->andReturn('PublicKeyFingerprint');

		$mockHttpRequest->shouldReceive('hasHeader')->with('X-Request-Signature')->andReturn(TRUE);
		$mockHttpRequest->shouldReceive('getHeader')->with('X-Request-Signature')->andReturn('SomeIdentifier' . ':' . base64_encode('ValidSignature'));
		$mockHttpRequest->shouldReceive('getHeader')->with('Content-Type')->andReturn('application/json');

		$signData = 'POST' . chr(10) . 'Request content' . chr(10) . 'application/json' . chr(10) . 'http://test/sso?foo=bar';
		$mockRsaWalletService
			->shouldReceive('verifySignature')
			->with($signData, 'ValidSignature', 'PublicKeyFingerprint')
			->andReturn(TRUE)
			->once();

		$result = $pattern->matchRequest($mockActionRequest);
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function matchRequestWithRequestAndInvalidSignatureHeaderReturnsTrue() {
		$pattern = new \TYPO3\SingleSignOn\Client\Security\RequestPattern\SignedRequestPattern();

		$mockRsaWalletService = m::mock('TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface');
		$this->inject($pattern, 'rsaWalletService', $mockRsaWalletService);

		$mockPublicKeyResolver = m::mock('TYPO3\SingleSignOn\Client\Security\RequestPattern\PublicKeyResolverInterface');
		$this->inject($pattern, 'publicKeyResolver', $mockPublicKeyResolver);

		$mockHttpRequest = m::mock('TYPO3\Flow\Http\Request', array(
			'getMethod' => 'POST',
			'getContent' => 'Request content',
			'getUri' => new Uri('http://test/sso?foo=bar')
		));
		$mockActionRequest = m::mock('TYPO3\Flow\Mvc\ActionRequest', array(
			'getHttpRequest' => $mockHttpRequest
		));

		$mockPublicKeyResolver->shouldReceive('resolveFingerprintByIdentifier')->with('SomeIdentifier')->andReturn('PublicKeyFingerprint');

		$mockHttpRequest->shouldReceive('hasHeader')->with('X-Request-Signature')->andReturn(TRUE);
		$mockHttpRequest->shouldReceive('getHeader')->with('X-Request-Signature')->andReturn('SomeIdentifier' . ':' . base64_encode('InvalidSignature'));
		$mockHttpRequest->shouldReceive('getHeader')->with('Content-Type')->andReturn('application/json');

		$mockRsaWalletService
			->shouldReceive('verifySignature')
			->with(m::any(), 'InvalidSignature', 'PublicKeyFingerprint')
			->andReturn(FALSE);

		$result = $pattern->matchRequest($mockActionRequest);
		$this->assertTrue($result);
	}

	/**
	 * Check for Mockery expectations
	 */
	public function tearDown() {
		m::close();
	}
}

?>