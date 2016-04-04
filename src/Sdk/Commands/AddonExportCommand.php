<?php
namespace Tygh\Sdk\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tygh\Sdk\Entities\Addon;

class AddonExportCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('addon:export')
            ->setDescription(
                'Moves all add-on files to the separate directory, preserving the structure of directories.'
            )
            ->addArgument('name',
                InputArgument::REQUIRED,
                'Add-on ID (name)'
            )
            ->addArgument('cart-directory',
                InputArgument::REQUIRED,
                'Path to CS-Cart installation directory'
            )
            ->addArgument('addon-directory',
                InputArgument::REQUIRED,
                'Path to directory where files should be moved to'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $addon_id = $input->getArgument('name');
        $abs_cart_path = rtrim(realpath($input->getArgument('cart-directory')), '\\/') . '/';

        if (!is_dir($input->getArgument('addon-directory'))) {
            mkdir($input->getArgument('addon-directory'), 0755, true);
        }
        $abs_addon_path = rtrim(realpath($input->getArgument('addon-directory')), '\\/') . '/';

        $addon = new Addon($addon_id, $abs_cart_path);
        $addon_files_glob_masks = $addon->getFilesGlobMasks();
        $glob_matches = $addon->matchFilesAgainstGlobMasks($addon_files_glob_masks, $abs_cart_path);

        foreach ($glob_matches as $rel_filepath) {
            $abs_cart_filepath = $abs_cart_path . $rel_filepath;
            $abs_addon_filepath = $abs_addon_path . $rel_filepath;

            if (!is_dir(dirname($abs_addon_filepath))) {
                mkdir(dirname($abs_addon_filepath), 0755, true);
            }

            $moved = rename($abs_cart_filepath, $abs_addon_filepath);

            $output->writeln(sprintf('Moving <%s> to %s<...> ... %s',
                $rel_filepath,
                $abs_addon_path,
                $moved ? '<info>OK</info>' : '<error>FAILED</error>'
            ));
        }
    }
}