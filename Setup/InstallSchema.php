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

namespace MSP\UserLockout\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    private function setupTableMain(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('msp_user_lockout');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn('msp_user_lockout_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entry ID'
            )
            ->addColumn('login',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Login'
            )
            ->addColumn('ip',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'IP'
            )
            ->addColumn('lockout_datetime',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Release date'
            );

        $setup->getConnection()->createTable($table);
    }

    private function setupTableFailure(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('msp_user_lockout_failure');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn('msp_user_lockout_failure_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entry ID'
            )
            ->addColumn('msp_user_lockout_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Lockout ID'
            )
            ->addColumn('date_time',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'First failure date time'
            )
            ->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('msp_user_lockout_failure'),
                    'msp_user_lockout_id',
                    $setup->getTable('msp_user_lockout'),
                    'msp_user_lockout_id'
                ),
                'msp_user_lockout_id',
                $setup->getTable('msp_user_lockout'),
                'msp_user_lockout_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);
    }

    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $this->setupTableMain($setup);
        $this->setupTableFailure($setup);

        $setup->endSetup();
    }
}