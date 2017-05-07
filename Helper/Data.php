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

namespace MSP\UserLockout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const XML_PATH_GENERAL_ENABLED = 'msp_securitysuite/userlockout/enabled';
    const XML_PATH_GENERAL_FAILURE_COUNT = 'msp_securitysuite/userlockout/failure_count';
    const XML_PATH_GENERAL_FAILURE_INTERVAL = 'msp_securitysuite/userlockout/failure_interval';
    const XML_PATH_GENERAL_FAILURE_PENALTY = 'msp_securitysuite/userlockout/failure_penalty';

    /**
     * Get lockout error
     * @param $interval
     * @return string
     */
    public function getLockoutError($interval)
    {
        return __('Too many login failures. Your account has been temporary locked. Try again in %1.', $interval);
    }

    /**
     * Get max failure count
     * @return int
     */
    public function getFailureCount()
    {
        return max((int) $this->scopeConfig->getValue(self::XML_PATH_GENERAL_FAILURE_COUNT), 1);
    }

    /**
     * Get failure interval in seconds
     * @return int
     */
    public function getFailureInterval()
    {
        return max((int) $this->scopeConfig->getValue(self::XML_PATH_GENERAL_FAILURE_INTERVAL), 5);
    }

    /**
     * Get failure interval in seconds
     * @return bool
     */
    public function getFailurePenalty()
    {
        return max((int) $this->scopeConfig->getValue(self::XML_PATH_GENERAL_FAILURE_PENALTY), 5);
    }

    /**
     * Return true if enabled
     * @return bool
     */
    public function getEnabled()
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_GENERAL_ENABLED);
    }
}
