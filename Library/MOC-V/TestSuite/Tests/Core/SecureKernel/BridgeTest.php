<?php
namespace MOC\V\TestSuite\Tests\Core\SecureKernel;

use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Core\SecureKernel
 */
class BridgeTest extends AbstractTestCase
{

    /**
     * @throws \MOC\V\Core\SecureKernel\Component\Exception\ComponentException
     */
    public function testSFTP()
    {

        $Bridge = new SFTP();

        $Bridge->openConnection('localhost', 22);
        try {
            $Bridge->loginCredentialKey('user', __FILE__, 'password');
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $Exception);
        }
        try {
            $Bridge->changeDirectory('.');
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $Exception);
        }
        $Bridge->closeConnection();

        $Bridge->openConnection('localhost', 21);
        try {
            $Bridge->loginCredential('user');
        } catch (\Exception $Exception) {
            if (!$Exception instanceof \PHPUnit_Framework_Error_Notice) {
                $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $Exception);
            }
        }
        try {
            $Bridge->changeDirectory('.');
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $Exception);
        }
        try {
            $Bridge->existsDirectory('.');
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $Exception);
        }
        $Bridge->closeConnection();

        $Bridge->openConnection('localhost', 21);
        try {
            $Bridge->loginCredential('user', 'password');
        } catch (\Exception $Exception) {
            if (!$Exception instanceof \PHPUnit_Framework_Error_Notice) {
                $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $Exception);
            }
        }
        try {
            $Bridge->changeDirectory('.');
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $Exception);
        }
        $Bridge->closeConnection();
    }
}
