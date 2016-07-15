<?php
namespace MSP\UserLockout\Command;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use MSP\UserLockout\Api\LockoutInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Unlock extends Command
{
    protected $configInterface;
    protected $lockoutInterface;

    public function __construct(
        ConfigInterface $configInterface,
        LockoutInterface $lockoutInterface
    ) {
        $this->configInterface = $configInterface;
        $this->lockoutInterface = $lockoutInterface;

        parent::__construct();
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

        $this->lockoutInterface->reset($login, $ip);
    }
}
