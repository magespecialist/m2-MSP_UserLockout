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

namespace MSP\UserLockout\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Stdlib\DateTime;
use MSP\UserLockout\Api\LockoutInterface;
use MSP\UserLockout\Helper\Data;

class Lockout extends AbstractModel implements LockoutInterface
{
    protected $helperData;
    protected $dateTime;
    protected $dtDateTime;

    public function __construct(
        Context $context,
        Registry $registry,
        Data $helperData,
        DateTime $dateTime,
        DateTime\DateTime $dtDateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->dateTime = $dateTime;
        $this->dtDateTime = $dtDateTime;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('MSP\UserLockout\Model\ResourceModel\Lockout');
    }

    /**
     * Get lockout interval as string
     * @param $login
     * @param $ip
     * @return string|false
     */
    public function getIntervalAsString($login, $ip)
    {
        $releaseTime = $this->getResource()->getLockoutReleaseTime($login, $ip);
        if (!$releaseTime) {
            return false;
        }

        $now = $this->dtDateTime->gmtTimestamp();
        $releaseTs = $this->dateTime->strToTime($releaseTime);

        $delta = $releaseTs - $now;

        if ($delta > 3600) {
            return __('%1:%2 hours', intval($delta / 3600), str_pad($delta % 3600, 2, '0', STR_PAD_LEFT));
        }

        if ($delta > 60) {
            return __('%1 minutes', 1 + intval($delta / 60));
        }

        return __('%1 seconds', 1 + $delta);
    }

    /**
     * Register a login failure
     * @param $login
     * @param $ip
     * @return bool
     */
    public function registerFailure($login, $ip)
    {
        return $this->getResource()->registerFailure($login, $ip);
    }

    /**
     * Reset failures registry
     * @param $login
     * @param $ip
     * @return LockoutInterface
     */
    public function reset($login, $ip)
    {
        $this->getResource()->reset($login, $ip);
        return $this;
    }

    /**
     * Return true if user is locked out
     * @param $login
     * @param $ip
     * @return bool
     */
    public function isLockedOut($login, $ip)
    {
        if (!$this->helperData->getEnabled()) {
            return false;
        }

        return $this->getResource()->isLockedOut($login, $ip);
    }

    /**
     * Release all locks
     * @return LockoutInterface
     */
    public function releaseAll()
    {
        $this->getResource()->releaseAll();
        return $this;
    }

    /**
     * Release current lock
     * @return LockoutInterface
     */
    public function release()
    {
        $this->delete();
        return $this;
    }
}
