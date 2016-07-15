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

namespace MSP\UserLockout\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Model\ResourceModel\Db\Context;
use MSP\UserLockout\Helper\Data;

class Lockout extends AbstractDb
{
    protected $dateTime;
    protected $helperData;

    protected $_failureTableName = 'msp_user_lockout_failure';
    
    public function __construct(
        Context $context,
        DateTime $dateTime,
        Data $helperData,
        $connectionName = null
    ) {
        $this->dateTime = $dateTime;
        $this->helperData = $helperData;
        
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('msp_user_lockout', 'msp_user_lockout_id');
    }

    /**
     * Filter login
     * @param $login
     * @return string
     */
    protected function _filterLogin($login)
    {
        return trim(strtolower($login));
    }

    /**
     * Get failure table name
     * @return string
     */
    public function getFailureTableName()
    {
        return $this->getTable($this->_failureTableName);
    }

    /**
     * Return true if there is at least one failure
     * @param $login
     * @param $ip
     * @return int
     */
    public function getFailuresCountInInterval($login, $ip)
    {
        $login = $this->_filterLogin($login);

        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $failureTable = $this->getFailureTableName();

        $ts = $this->dateTime->gmtTimestamp() - $this->helperData->getFailureInterval();

        $qry = $connection->select()
            ->from($table, 'COUNT(*)')
            ->joinLeft($failureTable, $table.'.msp_user_lockout_id = '.$failureTable.'.msp_user_lockout_id')
            ->where(
                'login = ' . $connection->quote($login) . ' AND '
                . 'ip = ' . $connection->quote($ip) . ' AND '
                . 'date_time >= ' . $connection->quote($this->dateTime->gmtDate(null, $ts))
            );

        return intval($connection->fetchOne($qry));
    }

    /**
     * Reset failures registry
     * @param $login
     * @return Lockout
     */
    public function reset($login, $ip)
    {
        $table = $this->getMainTable();

        $connection = $this->getConnection();
        $connection->delete($table,
            'login = ' . $connection->quote($login) . ' AND '
            . ' ip = ' . $connection->quote($ip)
        );

        return $this;
    }

    /**
     * Get lockout ID
     * @param $login
     * @param $ip
     * @return int|false
     */
    protected function _getLockoutId($login, $ip)
    {
        $row = $this->_getLockoutInfo($login, $ip);
        if ($row) {
            return $row['msp_user_lockout_id'];
        }

        return false;
    }

    /**
     * Get lockout information
     * @param $login
     * @param $ip
     * @return array|false
     */
    protected function _getLockoutInfo($login, $ip)
    {
        $table = $this->getMainTable();
        $login = $this->_filterLogin($login);

        $connection = $this->getConnection();
        $qry = $connection->select()
            ->from($table, '*')
            ->where(
                'login = ' . $connection->quote($login) . ' AND '
                . ' ip = ' . $connection->quote($ip)
            );

        return $connection->fetchRow($qry);
    }

    /**
     * Get lockout release time
     * @param $login
     * @param $ip
     * @return int|bool
     */
    public function getLockoutReleaseTime($login, $ip)
    {
        $row = $this->_getLockoutInfo($login, $ip);

        if ($row) {
            $lockoutTs = $row['lockout_datetime'];

            return $lockoutTs ?: false;
        }

        return false;
    }

    /**
     * Register a login failure
     * @param $login
     * @return bool
     */
    public function registerFailure($login, $ip)
    {
        $login = $this->_filterLogin($login);

        $connection = $this->getConnection();
        
        $lockoutId = $this->_getLockoutId($login, $ip);
        if (!$lockoutId) {
            $table = $this->getMainTable();
            $connection->insert($table, [
                'login' => $login,
                'ip' => $ip,
            ]);
            $lockoutId = $this->_getLockoutId($login, $ip);
        }

        $table = $this->getFailureTableName();
        $connection->insert($table, [
            'date_time' => $this->dateTime->gmtDate(),
            'msp_user_lockout_id' => $lockoutId,
        ]);

        $failuresCount = $this->getFailuresCountInInterval($login, $ip);
        if ($failuresCount >= $this->helperData->getFailureCount()) {
            $this->lockAccount($login, $ip);
            return true;
        }

        return false;
    }

    /**
     * Activate user's lockout
     * @param $login
     * @param $ip
     * @return Lockout
     */
    public function lockAccount($login, $ip)
    {
        $lockoutId = $this->_getLockoutId($login, $ip);
        if ($lockoutId) {
            $connection = $this->getConnection();

            $lockoutTs = $this->dateTime->gmtTimestamp() + $this->helperData->getFailurePenalty();

            $table = $this->getMainTable();
            $connection->update($table, [
                'lockout_datetime' => $this->dateTime->gmtDate(null, $lockoutTs),
            ], 'msp_user_lockout_id = ' . intval($lockoutId));
        }

        return $this;
    }

    /**
     * Return true if user is locked out
     * @param $login
     * @return bool
     */
    public function isLockedOut($login, $ip)
    {
        $lockoutTs = $this->getLockoutReleaseTime($login, $ip);

        if ($lockoutTs) {
            $now = $this->dateTime->gmtDate();
            return ($now <= $lockoutTs);
        }

        return false;
    }

    /**
     * Release all locks
     * @return $this
     */
    public function releaseAll()
    {
        $connection = $this->getConnection();

        $table = $this->getMainTable();
        $failureTable = $this->getFailureTableName();

        $connection->delete($failureTable);
        $connection->delete($table);

        return $this;
    }
}
