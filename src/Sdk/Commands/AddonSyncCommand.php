<?php
namespace Tygh\Sdk\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

class AddonSyncCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('addon:sync')
            ->setDescription(
                'Synchronizes add-on files between CS-Cart installation directory and the separate directory storing'
                . ' all add-on files. Calling this command has the same effect as calling the "addon:export"'
                . ' and "addon:symlink" commands simultaneously.'
            )
            ->addArgument('name',
                InputArgument::REQUIRED,
                'Add-on ID (name)'
            )
            ->addArgument('addon-directory',
                InputArgument::REQUIRED,
                'Path to directory where files should be moved to'
            )
            ->addArgument('cart-directory',
                InputArgument::REQUIRED,
                'Path to CS-Cart installation directory'
            )
            ->addOption('relative',
                'r',
                InputOption::VALUE_NONE,
                'Created symlinks will have a relative path to the target file. By default the created symlinks have an absolute path to target.'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $addon_export_command = $this->getApplication()->find('addon:export');
        $addon_symlink_command = $this->getApplication()->find('addon:symlink');

        $arguments = array(
            'name' => $input->getArgument('name'),
            'addon-directory'    => $input->getArgument('addon-directory'),
            'cart-directory'    => $input->getArgument('cart-directory'),
        );

        $export_input = new ArrayInput($arguments);
        $symlink_input = new ArrayInput($arguments + ['--relative'  => $input->getOption('relative')]);
        $addon_export_command->run($export_input, $output);
        $output->writeln('');
        $addon_symlink_command->run($symlink_input, $output);
    }
}