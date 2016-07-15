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

class LoginPostPluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var  $model \MSP\UserLockout\Plugin\Model\AccountManagementPlugin */
    protected $model;

    /** @var  $requestInterfaceMock \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestInterfaceMock;

    /** @var  $lockoutInterfaceMock \MSP\UserLockout\Api\LockoutInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $lockoutInterfaceMock;

    /** @var  $lockoutInterfaceMock \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress|\PHPUnit_Framework_MockObject_MockObject */
    protected $remoteAddressMock;

    /** @var  $messageManagerMock \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    /** @var  $accountRedirectMock \Magento\Customer\Model\Account\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $accountRedirectMock;

    /** @var  $lockoutInterfaceMock \MSP\UserLockout\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $helperDataMock;

    /** @var  $lockoutInterfaceMock \Magento\Customer\Controller\Account\LoginPost|\PHPUnit_Framework_MockObject_MockObject */
    protected $loginPostMock;

    public function setUp()
    {
        $this->requestInterfaceMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();

        $this->lockoutInterfaceMock = $this->getMockBuilder('MSP\UserLockout\Api\LockoutInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->remoteAddressMock = $this->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->accountRedirectMock = $this->getMockBuilder('Magento\Customer\Model\Account\Redirect')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->helperDataMock = $this->getMockBuilder('MSP\UserLockout\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->loginPostMock = $this->getMockBuilder('Magento\Customer\Controller\Account\LoginPost')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            'MSP\UserLockout\Plugin\Controller\Account\LoginPostPlugin',
            [
                'requestInterface' => $this->requestInterfaceMock,
                'lockoutInterface' => $this->lockoutInterfaceMock,
                'remoteAddress' => $this->remoteAddressMock,
                'messageManager' => $this->messageManagerMock,
                'accountRedirect' => $this->accountRedirectMock,
                'helperData' => $this->helperDataMock,
            ]
        );
    }

    public function testAroundExecuteDisabled()
    {
        $this->helperDataMock->expects($this->atLeastOnce())->method('getEnabled')->willReturn(false);

        $proceedResult = 'expectedResult';
        $proceed = function () use ($proceedResult) {
            return $proceedResult;
        };

        $this->assertEquals($proceedResult, $this->model->aroundExecute(
            $this->loginPostMock,
            $proceed
        ));
    }

    public function testAroundExecuteEnabled()
    {
        $this->helperDataMock->expects($this->atLeastOnce())->method('getEnabled')->willReturn(true);
        $this->lockoutInterfaceMock->expects($this->atLeastOnce())->method('isLockedOut')->willReturn(false);
        $this->requestInterfaceMock->expects($this->atLeastOnce())->method('getPost')->willReturn([
            'username' => 'john',
        ]);

        $proceedResult = 'expectedResult';
        $proceed = function () use ($proceedResult) {
            return $proceedResult;
        };

        $this->assertEquals($proceedResult, $this->model->aroundExecute(
            $this->loginPostMock,
            $proceed
        ));
    }

    public function testAroundExecuteLocked()
    {
        $phraseMock = $this->getMockBuilder('Magento\Framework\Phrase')->disableOriginalConstructor()->getMock();

        $expectedRedirect = 'expectedRedirect';

        $this->helperDataMock->expects($this->atLeastOnce())->method('getEnabled')->willReturn(true);
        $this->lockoutInterfaceMock->expects($this->atLeastOnce())->method('isLockedOut')->willReturn(true);
        $this->helperDataMock->expects($this->atLeastOnce())->method('getLockoutError')->willReturn($phraseMock);
        $this->messageManagerMock->expects($this->once())->method('addError');
        $this->accountRedirectMock->expects($this->once())->method('getRedirect')->willReturn($expectedRedirect);
        $this->requestInterfaceMock->expects($this->atLeastOnce())->method('getPost')->willReturn([
            'username' => 'john',
        ]);

        $proceed = function () {
            return 'foo';
        };

        $this->assertEquals($expectedRedirect, $this->model->aroundExecute(
            $this->loginPostMock,
            $proceed
        ));
    }
}
