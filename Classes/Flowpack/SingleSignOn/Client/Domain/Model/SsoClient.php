<?php
namespace Flowpack\SingleSignOn\Client\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * SSO client
 *
 * Will be configured using settings.
 */
class SsoClient {

	/**
	 * The key pair uuid
	 * @var string
	 */
	protected $keyPairUuid;

	/**
	 * The client identifier
	 * @var string
	 */
	protected $serviceBaseUri;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
	 */
	protected $rsaWalletService;

	/**
	 * Decrypt the access token cipher on callback to the client
	 *
	 * @param string $accessTokenCipher The access token ciphertext from the callback URI arguments
	 * @return string The decrypted access token or an empty string if the access token could not be decrypted
	 */
	public function decryptCallbackAccessToken($accessTokenCipher) {
		return $this->rsaWalletService->decrypt($accessTokenCipher, $this->keyPairUuid);
	}

	/**
	 * @param string $identifier
	 */
	public function setServiceBaseUri($identifier) {
		$identifier = rtrim($identifier, '/') . '/';
		$this->serviceBaseUri = $identifier;
	}

	/**
	 * @return string
	 */
	public function getServiceBaseUri() {
		return $this->serviceBaseUri;
	}

	/**
	 * @param string $keyPairUuid
	 */
	public function setKeyPairUuid($keyPairUuid) {
		$this->keyPairUuid = $keyPairUuid;
	}

	/**
	 * @return string
	 */
	public function getKeyPairUuid() {
		return $this->keyPairUuid;
	}

}
?>