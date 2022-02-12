<?php
namespace Tygh\Sdk\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Tygh\Sdk\Commands\Traits\TwigEnvironmentTrait;
use Tygh\Sdk\Entities\Addon;
use Twig\Environment;

class AddonCreateCommand extends Command
{
    use TwigEnvironmentTrait;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('addon:create')
            ->setDescription(
                "Creates addon directory structure and xml/.po files."
            )
            ->setHelp(
                " * Existing directories and files will be not overwritten.\n".
                " * Default templates are located in `cscart-sdk/templates/addon`.\n".
                " * User can create own templates in `~/.cscart-sdk/templates/addon`."
            )
            ->addArgument('name',
                InputArgument::REQUIRED,
                'Add-on ID (name)'
            )
            ->addArgument('addon-directory',
                InputArgument::REQUIRED,
                'Path to addon directory.'
            )
            ->addOption(
                'theme',
                't',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Themes',
                array('responsive')
            )
            ->addOption(
                'language',
                'l',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Languages',
                array('en','ru')
            )
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $addon_id = $input->getArgument('name');
        $addon_directory = $input->getArgument('addon-directory');
        $themes = $input->getOption('theme');
        $languages = $input->getOption('language');

        $fs->mkdir($addon_directory, 0755);

        $abs_addon_path = rtrim(realpath($input->getArgument('addon-directory')), '\\/') . '/';

        $output->writeln(sprintf('<fg=magenta;options=bold>Addon: id: "%s", themes: "%s", languages: "%s", dir: "%s"</>',
            $addon_id,
            implode(',',$themes),
            implode(',',$languages),
            $abs_addon_path
        ));

        $addon = new Addon($addon_id, $addon_directory);
        $addon_files_glob_masks = $addon->getFilesGlobMasks();

        $dirs_to_create = array();

        foreach ($addon_files_glob_masks as $mask){
            // Ignore "design/themes/" masks
            if (mb_strpos($mask, 'design/themes/') === 0) {
                continue;
            }

            // Skip .po files
            if (mb_strpos($mask, 'var/langs/') === 0) {
                continue;
            }

            // Special process of the var/themes_repository
            if (mb_strpos($mask, 'var/themes_repository/') === 0) {
                foreach ($themes as $theme) {
                    $dir = str_replace('/**/', '/' . $theme . '/', $mask);
                    $dirs_to_create[] = $dir;
                }
                continue;
            }

            $dirs_to_create[] = $mask;
        }

        // Sort for more nice output
        sort($dirs_to_create);

        foreach ($dirs_to_create as $dir){
            $dir_full_path = $addon_directory . '/' . $dir;
            if (file_exists($dir_full_path)){
                $output->writeln(sprintf('Directory already exists "%s", <info>skipping</info>',
                    $dir
                ));
            } else {
                $fs->mkdir($dir_full_path, 0755);
                $output->writeln(sprintf('Created directory "%s"  <info>OK</info>',
                    $dir_full_path
                ));
            }
        }

        $manifest_file = $addon->getXmlSchemePath();
        if (file_exists($manifest_file)) {
            $output->writeln(sprintf('Manifest already exists "%s", <info>skipping</info>',
                $manifest_file
            ));
        } else {
            $manifest_content = $this->twig()->render('addon/addon.xml.twig', [
                'addon_id' => $addon_id
            ]);
            file_put_contents($manifest_file, $manifest_content);
            $output->writeln(sprintf('Created manifest "%s"  <info>OK</info>',
                $manifest_file
            ));
        }

        foreach ($languages as $lang){
            $po_file = "var/langs/$lang/addons/$addon_id.po";
            $po_file_full_path = $addon_directory . '/' . $po_file;
            $lang_dir_full_path = dirname($po_file_full_path);
            if (file_exists($po_file_full_path)){
                $output->writeln(sprintf('Translation already exists "%s", <info>skipping</info>',
                    $po_file_full_path
                ));
            } else {
                if (!file_exists($lang_dir_full_path)){
                    $fs->mkdir($lang_dir_full_path, 0755);
                    $output->writeln(sprintf('Created directory "%s"  <info>OK</info>',
                        $lang_dir_full_path
                    ));
                }
                $po_content = $this->twig()->render('addon/lang/'.$lang.'.po.twig', [
                    'addon_id' => $addon_id
                ]);
                file_put_contents($po_file_full_path, $po_content);
                $output->writeln(sprintf('Created language "%s" file: "%s"  <info>OK</info>',
                    $lang,
                    $po_file_full_path
                ));
            }
        }
    }
}