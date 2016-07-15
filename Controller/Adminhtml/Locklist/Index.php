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
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $pageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;

        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MSP_UserLockout::list');
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $result */
        $result = $this->pageFactory->create();

        $result->setActiveMenu('MSP_UserLockout::locklist');
        $result->addBreadcrumb(__("User Lockout"), __("User Lockout"));
        $result->getConfig()->getTitle()->prepend(__("User Lockout"));

        return $result;
    }
}
