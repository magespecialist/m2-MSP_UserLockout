<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_UserLockout
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\User\Test\Unit\Helper;

class AccountManagementPluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var  $model \MSP\UserLockout\Plugin\Model\AccountManagementPlugin */
    protected $model;

    /** @var  $lockoutInterfaceMock \MSP\UserLockout\Api\LockoutInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $lockoutInterfaceMock;

    /** @var  $lockoutInterfaceMock \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress|\PHPUnit_Framework_MockObject_MockObject */
    protected $remoteAddressMock;

    /** @var  $lockoutInterfaceMock \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $httpMock;

    /** @var  $lockoutInterfaceMock \MSP\UserLockout\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $helperDataMock;

    /** @var  $accountManagementMock \Magento\Customer\Model\AccountManagement|\PHPUnit_Framework_MockObject_MockObject */
    protected $accountManagementMock;

    public function setUp()
    {
        $this->lockoutInterfaceMock = $this->getMockBuilder('MSP\UserLockout\Api\LockoutInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->remoteAddressMock = $this->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->httpMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->helperDataMock = $this->getMockBuilder('MSP\UserLockout\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->accountManagementMock = $this->getMockBuilder('Magento\Customer\Model\AccountManagement')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            'MSP\UserLockout\Plugin\Model\AccountManagementPlugin',
            [
                'lockoutInterface' => $this->lockoutInterfaceMock,
                'remoteAddress' => $this->remoteAddressMock,
                'http' => $this->httpMock,
                'helperData' => $this->helperDataMock
            ]
        );
    }

    public function testAroundAuthenticateDisabled()
    {
        $this->helperDataMock->expects($this->atLeastOnce())->method('getEnabled')->willReturn(false);

        $proceedResult = 'expectedResult';
        $proceed = function ($username, $password) use ($proceedResult) {
            return $proceedResult;
        };

        $this->assertEquals($proceedResult, $this->model->aroundAuthenticate(
            $this->accountManagementMock,
            $proceed,
            'john',
            'doe'
        ));
    }

    public function testAroundAuthenticateLocked()
    {
        $phraseMock = $this->getMockBuilder('Magento\Framework\Phrase')->disableOriginalConstructor()->getMock();

        $proceedResult = 'expectedResult';
        $proceed = function ($username, $password) use ($proceedResult) {
            return $proceedResult;
        };

        $this->helperDataMock->expects($this->atLeastOnce())->method('getEnabled')->willReturn(true);
        $this->lockoutInterfaceMock->expects($this->atLeastOnce())->method('isLockedOut')->willReturn(true);
        $this->helperDataMock->expects($this->atLeastOnce())->method('getLockoutError')->willReturn($phraseMock);

        $this->setExpectedException('Magento\Framework\Exception\InvalidEmailOrPasswordException');

        $this->model->aroundAuthenticate(
            $this->accountManagementMock,
            $proceed,
            'john',
            'doe'
        );
    }

    public function testAroundAuthenticateReset()
    {
        $proceedResult = 'expectedResult';
        $proceed = function ($username, $password) use ($proceedResult) {
            return $proceedResult;
        };

        $this->helperDataMock->expects($this->atLeastOnce())->method('getEnabled')->willReturn(true);
        $this->lockoutInterfaceMock->expects($this->atLeastOnce())->method('isLockedOut')->willReturn(false);
        $this->lockoutInterfaceMock->expects($this->once())->method('reset');

        $this->assertEquals($proceedResult, $this->model->aroundAuthenticate(
            $this->accountManagementMock,
            $proceed,
            'john',
            'doe'
        ));
    }

    public function testAroundAuthenticateRegisterFailure()
    {
        $proceedResult = 'expectedResult';
        $proceed = function ($username, $password) use ($proceedResult) {
            throw new \Exception('');
        };

        $this->helperDataMock->expects($this->atLeastOnce())->method('getEnabled')->willReturn(true);
        $this->lockoutInterfaceMock->expects($this->atLeastOnce())->method('isLockedOut')->willReturn(false);
        $this->lockoutInterfaceMock->expects($this->once())->method('registerFailure');

        $this->setExpectedException('\Exception');

        $this->assertEquals($proceedResult, $this->model->aroundAuthenticate(
            $this->accountManagementMock,
            $proceed,
            'john',
            'doe'
        ));
    }
}
