# MSP UserLockout

Customer login **brute force protection module**.

This module can temporary lock a customer account when too many login password attempts fail are detected.

> Member of **MSP Security Suite**
>
> See: https://github.com/magespecialist/m2-MSP_SecuritySuiteFull

## Installing on Magento2:

**1. Install using composer**

From command line: 

`composer require msp/userlockout`<br />
`php bin/magento setup:upgrade`

**2. Enable and configure from your Magento backend config**

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_UserLockout/master/screenshots/config.png" />

## Frontend screenshot

When the amount of failed attempts is reached, this module prevents further attemps for a defined amount of seconds.

This is one of the most effective countermeasures for brute force.

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_UserLockout/master/screenshots/too_many_failures.png" />

## Backend manual unlock

You can monitor and manually unlock users from your Magento backend under **Customers > Locked Users**:

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_UserLockout/master/screenshots/lockout_list.png" />

## Command-line unlock

You can manually unlock one user from command-line if necessary:

`php bin/magento msp:security:lockout:unlock <IP> <username>`

Example:

`php bin/magento msp:security:lockout:unlock 127.0.0.1 user@example.com`
