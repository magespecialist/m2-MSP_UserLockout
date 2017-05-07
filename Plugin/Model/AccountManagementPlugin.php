<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_UserLockout
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\UserLockout\Plugin\Model;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use MSP\UserLockout\Api\LockoutInterface;
use MSP\UserLockout\Helper\Data;

class AccountManagementPlugin
{
    /**
     * @var LockoutInterface
     */
    private $lockout;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var Http
     */
    private $http;

    /**
     * @var Data
     */
    private $helperData;

    public function __construct(
        LockoutInterface $lockout,
        RemoteAddress $remoteAddress,
        Http $http,
        Data $helperData
    ) {
        $this->lockout = $lockout;
        $this->remoteAddress = $remoteAddress;
        $this->http = $http;
        $this->helperData = $helperData;
    }

    public function aroundAuthenticate(
        AccountManagement $subject,
        \Closure $procede,
        $username,
        $password
    ) {
        if (!$this->helperData->getEnabled()) {
            return $procede($username, $password);
        }

        $ip = $this->remoteAddress->getRemoteAddress();

        if ($this->lockout->isLockedOut($username, $ip)) {
            $interval = $this->lockout->getIntervalAsString($username, $ip);
            $errorMessage = $this->helperData->getLockoutError($interval);

            throw new InvalidEmailOrPasswordException($errorMessage);
        }

        $exception = null;
        try {
            $res = $procede($username, $password);
            $this->lockout->reset($username, $ip);
            return $res;

        } catch (\Exception $e) {
            $exception = $e;
            $this->lockout->registerFailure($username, $ip);
        }

        throw $exception;
    }
}
