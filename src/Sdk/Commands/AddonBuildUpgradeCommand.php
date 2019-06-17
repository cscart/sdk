<?php

namespace Tygh\Sdk\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use upgrade\builder\AddonBuilder;
use upgrade\builder\AddonReleaseArchive;

class AddonBuildUpgradeCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('addon:build_upgrade')
            ->setDescription(
                'Creates upgrade package between add-on versions'
            )
            ->addArgument('old_addon_version_archive_path',
                InputArgument::REQUIRED,
                'Old add-on version archive path'
            )
            ->addArgument('new_addon_version_archive_path',
                InputArgument::REQUIRED,
                'New add-on version archive path'
            )
            ->addArgument('result_dir_path',
                InputArgument::REQUIRED,
                'Path to a directory where the built upgrade package will be placed'
            )
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $old_archive_path = $input->getArgument('old_addon_version_archive_path');
        $new_archive_path = $input->getArgument('new_addon_version_archive_path');
        $result_dir_path = $input->getArgument('result_dir_path');

        if (!$fs->exists($old_archive_path)) {
            throw new FileNotFoundException($old_archive_path);
        }
        if (!$fs->exists($new_archive_path)) {
            throw new FileNotFoundException($new_archive_path);
        }

        $fs->mkdir($result_dir_path, 0755);

        $old_version_archive = new AddonReleaseArchive($old_archive_path);
        $new_version_archive = new AddonReleaseArchive($new_archive_path);

        $addon_upgrade_builder = new AddonBuilder($old_version_archive, $new_version_archive, $result_dir_path);
        $addon_upgrade_builder->initPaths();

        $addon_upgrade_builder->run();
    }
}
