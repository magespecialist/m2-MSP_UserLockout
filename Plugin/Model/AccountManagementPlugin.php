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

namespace MSP\UserLockout\Plugin\Model;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use MSP\UserLockout\Api\LockoutInterface;
use MSP\UserLockout\Helper\Data;

class AccountManagementPlugin
{
    protected $lockoutInterface;
    protected $remoteAddress;
    protected $helperData;
    protected $http;

    public function __construct(
        LockoutInterface $lockoutInterface,
        RemoteAddress $remoteAddress,
        Http $http,
        Data $helperData
    ) {
        $this->lockoutInterface = $lockoutInterface;
        $this->remoteAddress = $remoteAddress;
        $this->helperData = $helperData;
        $this->http = $http;
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

        if ($this->lockoutInterface->isLockedOut($username, $ip)) {
            $interval = $this->lockoutInterface->getIntervalAsString($username, $ip);
            $errorMessage = $this->helperData->getLockoutError($interval);

            throw new InvalidEmailOrPasswordException($errorMessage);
        }

        $exception = null;
        try {
            $res = $procede($username, $password);
            $this->lockoutInterface->reset($username, $ip);
            return $res;

        } catch (\Exception $e) {
            $exception = $e;
            $this->lockoutInterface->registerFailure($username, $ip);
        }

        throw $exception;
    }
}
