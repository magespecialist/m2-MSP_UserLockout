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

namespace MSP\UserLockout\Command;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use MSP\UserLockout\Api\LockoutInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Unlock extends Command
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LockoutInterface
     */
    private $lockout;

    public function __construct(
        ConfigInterface $config,
        LockoutInterface $lockout
    ) {
        parent::__construct();
        $this->config = $config;
        $this->lockout = $lockout;
    }

    protected function configure()
    {
        $this->setName('msp:security:lockout:unlock');
        $this->setDescription('Unlock user');

        $this->addArgument('ip', InputArgument::REQUIRED, __('User IP'));
        $this->addArgument('login', InputArgument::REQUIRED, __('User login'));

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ip = $input->getArgument('ip');
        $login = $input->getArgument('login');

        $this->lockout->reset($login, $ip);
    }
}
