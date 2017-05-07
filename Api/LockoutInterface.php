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

namespace MSP\UserLockout\Api;

interface LockoutInterface
{
    /**
     * Get lockout interval as string
     * @param $login
     * @param $ip
     * @return string
     */
    public function getIntervalAsString($login, $ip);

    /**
     * Register a login failure
     * @param $login
     * @param $ip
     * @return bool
     */
    public function registerFailure($login, $ip);

    /**
     * Reset failures registry
     * @param $login
     * @param $ip
     * @return LockoutInterface
     */
    public function reset($login, $ip);

    /**
     * Return true if user is locked out
     * @param $login
     * @param $ip
     * @return bool
     */
    public function isLockedOut($login, $ip);

    /**
     * Release all locks
     * @return LockoutInterface
     */
    public function releaseAll();

    /**
     * Release currenct lock
     * @return LockoutInterface
     */
    public function release();
}
