<?php
namespace Tygh\Sdk\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
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
            ->addArgument('addon-directory',
                InputArgument::REQUIRED,
                'Path to directory where files should be moved to'
            )
            ->addArgument('cart-directory',
                InputArgument::REQUIRED,
                'Path to CS-Cart installation directory'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $addon_id = $input->getArgument('name');
        $abs_cart_path = rtrim(realpath($input->getArgument('cart-directory')), '\\/') . '/';

        $fs->mkdir($input->getArgument('addon-directory'), 0755);

        $abs_addon_path = rtrim(realpath($input->getArgument('addon-directory')), '\\/') . '/';

        $output->writeln(sprintf('<fg=magenta;options=bold>Moving add-on files to the "%s" directory:</>',
            $abs_addon_path
        ));

        $addon = new Addon($addon_id, $abs_cart_path);
        $addon_files_glob_masks = $addon->getFilesGlobMasks();
        $glob_matches = $addon->matchFilesAgainstGlobMasks($addon_files_glob_masks, $abs_cart_path);

        $counter = 0;
        foreach ($glob_matches as $rel_filepath) {
            $abs_cart_filepath = $abs_cart_path . $rel_filepath;

            // Skip links pointing to target add-on directory
            if (is_link($abs_cart_filepath)) {
                $output->writeln(sprintf('Found symlink "%s", <info>skipping</info>',
                    $rel_filepath
                ));

                continue;
            }

            // Add-on templates at the "design/" directory will be
            // exported to the "var/themes_repository/" directory.
            if (mb_strpos($rel_filepath, 'design/themes/') === 0) {
                $abs_addon_filepath = $abs_addon_path
                    . 'var/themes_repository/'
                    . mb_substr($rel_filepath, mb_strlen('design/themes/'));
            } else {
                $abs_addon_filepath = $abs_addon_path . $rel_filepath;
            }

            if (file_exists($abs_addon_filepath)) {
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion(sprintf(
                    '<question>%s "%s" already exists. Overwrite? [y/N]:</question> ',
                    is_dir($abs_addon_filepath) ? 'Directory' : 'File',
                    $abs_addon_filepath
                ), false);

                if (!$helper->ask($input, $output, $question)) {
                    continue;
                }
            }

            $fs->mkdir(dirname($abs_addon_filepath), 0755);

            $output->write(sprintf('Moving "%s" to "%s" ... ',
                $rel_filepath,
                $abs_addon_filepath
            ));

            $fs->rename($abs_cart_filepath, $abs_addon_filepath, true);
            $counter++;
            $output->writeln('<info>OK</info>');
        }

        $output->writeln(sprintf('<options=bold>%u</> <info>files and directories have been moved.</info>',
            $counter
        ));
    }
}