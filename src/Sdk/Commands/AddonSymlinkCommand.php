<?php
namespace Tygh\Sdk\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Tygh\Sdk\Entities\Addon;

class AddonSymlinkCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('addon:symlink')
            ->setDescription(
                'Creates symlinks for add-on files at the CS-Cart installation directory,'
                . ' allowing you to develop and store add-on files in a separate Git repository.'
            )
            ->addArgument('name',
                InputArgument::REQUIRED,
                'Add-on ID (name)'
            )
            ->addArgument('addon-directory',
                InputArgument::REQUIRED,
                'Path to directory with add-on files'
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
        $fs = new Filesystem();

        $addon_id = $input->getArgument('name');

        $abs_cart_path = rtrim(realpath($input->getArgument('cart-directory')), '\\/') . '/';
        $abs_addon_path = rtrim(realpath($input->getArgument('addon-directory')), '\\/') . '/';

        chdir($abs_addon_path);

        $addon = new Addon($addon_id, $abs_addon_path);
        $addon_files_glob_masks = $addon->getFilesGlobMasks();
        $glob_matches = $addon->matchFilesAgainstGlobMasks($addon_files_glob_masks, $abs_addon_path);

        $output->writeln(sprintf('<fg=magenta;options=bold>Creating symlinks at the "%s" directory:</>',
            $abs_cart_path
        ));

        foreach ($glob_matches as $rel_filepath) {
            $abs_addon_filepath = $abs_addon_path . $rel_filepath;

            // Add-on templates at the "var/themes_repository/" directory will be
            // symlinked to the "design/themes/" directory.
            if (mb_strpos($rel_filepath, 'var/themes_repository/') === 0) {
                $abs_cart_filepath = $abs_cart_path
                    . 'design/themes/'
                    . mb_substr($rel_filepath, mb_strlen('var/themes_repository/'));
            } else {
                $abs_cart_filepath = $abs_cart_path . $rel_filepath;
            }

            // Delete existing files and links located at cart directory
            clearstatcache(true, $abs_cart_filepath);

            if (file_exists($abs_cart_filepath)) {
                $is_link = is_link($abs_cart_filepath);
                $is_file = $is_link ? is_file(readlink($abs_cart_filepath)) : is_file($abs_cart_filepath);
                $is_dir = $is_link ? is_dir(readlink($abs_cart_filepath)) : is_dir($abs_cart_filepath);

                // Confirm overwriting of the found conflicting file or directory on the same path.
                // We only ask confirmation for files which are not symbolic links, because we assume
                // that symbolic links were created by this command earlier and can be overwritten without
                // the loss of any data.
                if (!$is_link && ($is_file || $is_dir)) {
                    $helper = $this->getHelper('question');
                    $question = new ConfirmationQuestion(sprintf(
                        '<question>%s "%s" already exists. Overwrite? [y/N]:</question> ',
                        $is_dir ? 'Directory' : 'File',
                        $abs_cart_filepath
                    ), false);

                    if (!$helper->ask($input, $output, $question)) {
                        continue;
                    }
                }

                $fs->remove($abs_cart_filepath);
            }

            $symlink_target_filepath = $input->getOption('relative')
                ? $fs->makePathRelative(
                    dirname($abs_addon_filepath), dirname($abs_cart_filepath)
                ) . basename($abs_cart_filepath)
                : $abs_addon_filepath;

            $fs->symlink(
                $symlink_target_filepath,
                $abs_cart_filepath
            );

            $output->writeln(sprintf('Creating symlink for %s... <info>OK</info>',
                $rel_filepath
            ));
        }
    }
}