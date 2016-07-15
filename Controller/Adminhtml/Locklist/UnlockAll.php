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

namespace MSP\UserLockout\Controller\Adminhtml\Locklist;

use Magento\Backend\App\Action;
use MSP\UserLockout\Api\LockoutInterface;

class UnlockAll extends Action
{
    protected $lockoutInterface;

    public function __construct(
        Action\Context $context,
        LockoutInterface $lockoutInterface
    ) {
        $this->lockoutInterface = $lockoutInterface;

        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MSP_UserLockout::unlock');
    }

    public function execute()
    {
        $this->lockoutInterface->releaseAll();
        $this->messageManager->addSuccess('All locks have been released.');

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }
}
