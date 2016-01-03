<?php

namespace Redports\Node\Update;

use Herrera\Phar\Update\Exception\InvalidArgumentException;
use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use KevinGH\Version\Version;

/**
 * Manages the Phar update process.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class UpdateManager extends Manager
{
    /**
     * SHA256 hash of public key.
     *
     * @var string
     */
    private $publicKeyHash;

    /**
     * Sets the update manifest.
     *
     * @param Manifest $manifest The manifest.
     */
    public function __construct(Manifest $manifest)
    {
        parent::__construct($manifest);
    }

    /**
     * Returns the SHA256 hash of the publicKey file.
     *
     * @return string SHA256 hash.
     */
    public function getPublicKeyHash()
    {
        return $this->publicKeyHash;
    }

    /**
     * Sets the SHA256 hash of the publicKey file.
     * 
     * @param string $file SHA256 hash for publicKey
     * 
     * @throws Exception\Exception
     * @throws InvalidArgumentException If the hash is invalid.
     */
    public function setPublicKeyHash($hash)
    {
        if (strlen($hash) != 64) {
            throw InvalidArgumentException::create(
                'The hash has an invalid length'
            );
        }

        $this->publicKeyHash = $hash;
    }

    /**
     * Updates the running Phar if any is available and checks
     * the fingerprint of the public key.
     *
     * @param string|Version $version The current version.
     * @param bool           $major   Lock to current major version?
     * @param bool           $pre     Allow pre-releases?
     *
     * @return bool TRUE if an update was performed, FALSE if none available.
     */
    public function update($version, $major = false, $pre = false)
    {
        if (false === ($version instanceof Version)) {
            $version = Version::create($version);
        }

        if (null !== ($update = $this->getManifest()->findRecent(
            $version,
            $major,
            $pre
        ))) {
            $tmpfile = $update->getFile();

            if (null !== $this->getPublicKeyHash()) {
                if (false === is_file($tmpfile.'.pubkey')) {
                    echo "ALERT: Update not signed with public key!\n";
                    $update->deleteFile();

                    return false;
                }

                if (hash_file('sha256', $tmpfile.'.pubkey') !== $this->getPublicKeyHash()) {
                    echo "ALERT: Public key fingerprint mismatch!!!\n";
                    $update->deleteFile();

                    return false;
                }
            }

            $update->copyTo($this->getRunningFile());

            return true;
        }

        return false;
    }
}
