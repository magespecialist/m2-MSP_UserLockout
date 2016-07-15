<?php
/**
 * Created by PhpStorm.
 * User: Riccardo
 * Date: 27/06/2016
 * Time: 12:15
 */
namespace MSP\UserLockout\Plugin\Controller\Account;

use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use MSP\UserLockout\Api\LockoutInterface;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use MSP\UserLockout\Helper\Data;

class LoginPostPlugin
{
    protected $requestInterface;
    protected $lockoutInterface;
    protected $remoteAddress;
    protected $messageManager;
    protected $accountRedirect;
    protected $helperData;

    public function __construct(
        RequestInterface $requestInterface,
        LockoutInterface $lockoutInterface,
        RemoteAddress $remoteAddress,
        MessageManager $messageManager,
        AccountRedirect $accountRedirect,
        Data $helperData
    ) {
        $this->requestInterface = $requestInterface;
        $this->lockoutInterface = $lockoutInterface;
        $this->remoteAddress = $remoteAddress;
        $this->messageManager = $messageManager;
        $this->accountRedirect = $accountRedirect;
        $this->helperData = $helperData;
    }

    public function aroundExecute(
        LoginPost $subject,
        \Closure $procede
    ) {
        // Must do this because error description is not correctly handled in loginPost
        if ($this->helperData->getEnabled()) {
            $login = $this->requestInterface->getPost('login');

            if (!empty($login['username'])) {
                $username = $login['username'];
                $ip = $this->remoteAddress->getRemoteAddress();

                if ($this->lockoutInterface->isLockedOut($username, $ip)) {
                    $interval = $this->lockoutInterface->getIntervalAsString($username, $ip);
                    $errorMessage = $this->helperData->getLockoutError($interval);

                    $this->messageManager->addError($errorMessage);
                    return $this->accountRedirect->getRedirect();
                }
            }
        }

        return $procede();
    }
}
