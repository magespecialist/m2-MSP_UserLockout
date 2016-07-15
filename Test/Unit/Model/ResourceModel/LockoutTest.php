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

class LockoutTest extends \PHPUnit_Framework_TestCase
{
    /** @var \MSP\UserLockout\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $helperDataMock;

    /** @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTimeMock;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /** @var  \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dbAdapterMock;

    /** @var  \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $dbContextMock;

    protected function setUp()
    {
        $this->dateTimeMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->helperDataMock = $this->getMockBuilder('MSP\UserLockout\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->resourceMock = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->dbAdapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->dbContextMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\Context')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    public function testIsLockedOut()
    {
        /** @var $lockoutMock \MSP\UserLockout\Model\ResourceModel\Lockout|\PHPUnit_Framework_MockObject_MockObject */
        $lockoutMock = $this->getMockBuilder('MSP\UserLockout\Model\ResourceModel\Lockout')
            ->setConstructorArgs([
                'context' => $this->dbContextMock,
                'dateTime' => $this->dateTimeMock,
                'helperData' => $this->helperDataMock,
            ])
            ->setMethods(['getLockoutReleaseTime'])
            ->getMock();

        $login = 'my@email.com';
        $ip = '127.0.0.1';

        $this->dateTimeMock->expects($this->atLeastOnce())->method('gmtDate')
            ->willReturn('2012-01-01 01:00:00');

        $lockoutMock->expects($this->atLeastOnce())->method('getLockoutReleaseTime')
            ->will($this->onConsecutiveCalls('2012-01-01 02:00:00', '2012-01-01 00:00:00'));

        $this->assertEquals(true, $lockoutMock->isLockedOut($login, $ip));
        $this->assertEquals(false, $lockoutMock->isLockedOut($login, $ip));
    }

    public function testRegisterFailure()
    {
        $login = 'my@email.com';
        $ip = '127.0.0.1';

        /** @var $lockoutMock \MSP\UserLockout\Model\ResourceModel\Lockout|\PHPUnit_Framework_MockObject_MockObject */
        $lockoutMock = $this->getMockBuilder('MSP\UserLockout\Model\ResourceModel\Lockout')
            ->setConstructorArgs([
                'context' => $this->dbContextMock,
                'dateTime' => $this->dateTimeMock,
                'helperData' => $this->helperDataMock,
                'resource' => $this->resourceMock,
            ])
            ->setMethods([
                'getConnection',
                'getMainTable',
                'getFailureTableName',
                '_getLockoutId',
                'getFailuresCountInInterval',
                'lockAccount',
            ])
            ->getMock();

        $lockoutMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $lockoutMock->expects($this->atLeastOnce())->method('_getLockoutId')->willReturn(1);
        $lockoutMock->expects($this->atLeastOnce())->method('getFailuresCountInInterval')
            ->will($this->onConsecutiveCalls(1, 2, 3));

        $lockoutMock->expects($this->once())->method('lockAccount');

        $this->helperDataMock->expects($this->atLeastOnce())->method('getFailureCount')->willReturn(3);

        $this->assertEquals(false, $lockoutMock->registerFailure($login, $ip));
        $this->assertEquals(false, $lockoutMock->registerFailure($login, $ip));
        $this->assertEquals(true, $lockoutMock->registerFailure($login, $ip));
    }
}
