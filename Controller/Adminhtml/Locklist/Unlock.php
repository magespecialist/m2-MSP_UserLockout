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

namespace MSP\UserLockout\Controller\Adminhtml\Locklist;

use Magento\Backend\App\Action;
use MSP\UserLockout\Api\LockoutInterface;
use Magento\Ui\Component\MassAction\Filter;
use MSP\UserLockout\Model\ResourceModel\Lockout\CollectionFactory;

class Unlock extends Action
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LockoutInterface
     */
    private $lockout;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LockoutInterface $lockout,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->lockout = $lockout;
        $this->collectionFactory = $collectionFactory;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MSP_UserLockout::unlock');
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection as $lock) {
            /** @var $lock LockoutInterface */
            $lock->release();
        }

        $this->messageManager->addSuccessMessage('Selected locks have been released.');

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }
}
