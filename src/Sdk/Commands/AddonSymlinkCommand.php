<?php
namespace Tygh\Sdk\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
        $addon_id = $input->getArgument('name');

        $abs_cart_path = rtrim(realpath($input->getArgument('cart-directory')), '\\/') . '/';
        $abs_addon_path = rtrim(realpath($input->getArgument('addon-directory')), '\\/') . '/';

        chdir($abs_addon_path);

        $addon = new Addon($addon_id, $abs_addon_path);
        $addon_files_glob_masks = $addon->getFilesGlobMasks();
        $glob_matches = $addon->matchFilesAgainstGlobMasks($addon_files_glob_masks, $abs_addon_path);

        foreach ($glob_matches as $rel_filepath) {
            $abs_addon_filepath = $abs_addon_path . $rel_filepath;
            $abs_cart_filepath = $abs_cart_path . $rel_filepath;

            chdir(dirname($abs_cart_filepath));

            // Delete existing files and links located at cart directory
            clearstatcache(true, $abs_cart_filepath);

            $is_link = is_link($abs_cart_filepath);
            $is_file = $is_link ? is_file(readlink($abs_cart_filepath)) : is_file($abs_cart_filepath);
            $is_dir = $is_link ? is_dir(readlink($abs_cart_filepath)) : is_dir($abs_cart_filepath);

            if ($is_file || $is_link) {
                unlink($abs_cart_filepath);
            } elseif ($is_dir) {
                rmdir($abs_cart_filepath);
            }

            $symlink_target_filepath = $input->getOption('relative')
                ? $this->findRelativePath(dirname($abs_cart_filepath), $abs_addon_filepath)
                : $abs_addon_filepath;

            $symlink_created = symlink(
                $symlink_target_filepath,
                $abs_cart_filepath
            );

            $output->writeln(sprintf('Creating symlink for %s... %s',
                $rel_filepath,
                $symlink_created ? '<info>OK</info>' : '<error>FAILED</error>'
            ));
        }
    }

    /**
     * Find the relative file system path between two file system paths
     *
     * @param string $frompath Path to start from
     * @param string $topath Path we want to end up in
     *
     * @link https://gist.github.com/ohaal/2936041
     *
     * @return string Path leading from $frompath to $topath
     */
    protected function findRelativePath($frompath, $topath)
    {
        $from = explode(DIRECTORY_SEPARATOR, $frompath); // Folders/File
        $to = explode(DIRECTORY_SEPARATOR, $topath); // Folders/File
        $relpath = '';

        $i = 0;
        // Find how far the path is the same
        while (isset($from[$i]) && isset($to[$i])) {
            if ($from[$i] != $to[$i]) {
                break;
            }
            $i++;
        }
        $j = count($from) - 1;
        // Add '..' until the path is the same
        while ($i <= $j) {
            if (!empty($from[$j])) {
                $relpath .= '..' . DIRECTORY_SEPARATOR;
            }
            $j--;
        }
        // Go to folder from where it starts differing
        while (isset($to[$i])) {
            if (!empty($to[$i])) {
                $relpath .= $to[$i] . DIRECTORY_SEPARATOR;
            }
            $i++;
        }

        // Strip last separator
        return substr($relpath, 0, -1);
    }
}