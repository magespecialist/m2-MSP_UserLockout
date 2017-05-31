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

namespace MSP\UserLockout\Plugin\Controller\Account;

use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use MSP\SecuritySuiteCommon\Api\LogManagementInterface;
use MSP\UserLockout\Api\LockoutInterface;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\Event\ManagerInterface as EventInterface;
use MSP\UserLockout\Helper\Data;

class LoginPostPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LockoutInterface
     */
    private $lockout;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var EventInterface
     */
    private $event;

    public function __construct(
        RequestInterface $request,
        LockoutInterface $lockout,
        RemoteAddress $remoteAddress,
        MessageManager $messageManager,
        AccountRedirect $accountRedirect,
        EventInterface $event,
        Data $helperData
    ) {
        $this->request = $request;
        $this->lockout = $lockout;
        $this->remoteAddress = $remoteAddress;
        $this->messageManager = $messageManager;
        $this->accountRedirect = $accountRedirect;
        $this->helperData = $helperData;
        $this->event = $event;
    }

    public function aroundExecute(
        LoginPost $subject,
        \Closure $procede
    ) {
        // Must do this because error description is not correctly handled in loginPost
        if ($this->helperData->getEnabled()) {
            $login = $this->request->getPost('login');

            if (!empty($login['username'])) {
                $username = $login['username'];
                $ip = $this->remoteAddress->getRemoteAddress();

                if ($this->lockout->isLockedOut($username, $ip)) {
                    $this->event->dispatch(LogManagementInterface::EVENT_ACTIVITY, [
                        'module' => 'MSP_UserLockout',
                        'message' => 'Login attempt while locked',
                        'username' => $username,
                    ]);

                    $interval = $this->lockout->getIntervalAsString($username, $ip);
                    $errorMessage = $this->helperData->getLockoutError($interval);

                    $this->messageManager->addError($errorMessage);
                    return $this->accountRedirect->getRedirect();
                }
            }
        }

        return $procede();
    }
}
