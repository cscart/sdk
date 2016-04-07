<?php
namespace Tygh\Sdk\Commands\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

trait ValidateCartPathTrait
{
    protected function validateCartPath($abs_cart_path, InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($abs_cart_path . 'config.php') || !file_exists($abs_cart_path . 'config.local.php')) {
            $helper = $this->getHelper('question');
            $confirm_cart_path_question = new ConfirmationQuestion(sprintf(
                '<fg=white;bg=red;options=bold>'
                . 'Looks like the specified CS-Cart installation path "%s" is invalid.' . PHP_EOL
                . 'Please make sure that the "cart-directory"'
                . ' and "addon-directory" arguments are not messed up.' . PHP_EOL
                . 'Are you sure you want to proceed? [y/N]:</> ',
                $abs_cart_path
            ), false);

            if (!$helper->ask($input, $output, $confirm_cart_path_question)) {
                exit;
            }
        }
    }
}