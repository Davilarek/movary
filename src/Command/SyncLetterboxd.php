<?php declare(strict_types=1);

namespace Movary\Command;

use Movary\Application\Service\Letterboxd\SyncRatings;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncLetterboxd extends Command
{
    protected static $defaultName = 'app:sync-letterboxd';

    public function __construct(
        private readonly SyncRatings $syncRatings,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this
            ->setDescription('Sync letterboxd.com movie history and rating with local database')
            ->addArgument('ratingsCsvName', InputArgument::REQUIRED, 'Letterboxed rating csv file name (must be put into the tmp directory)');
    }

    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $ratingsCsvPath = __DIR__ . '/../../tmp/' . $input->getArgument('ratingsCsvName');

        if (is_dir($ratingsCsvPath) === true || is_readable($ratingsCsvPath) === false) {
            $output->writeln('Csv file at the given path cannot be read: ' . $ratingsCsvPath);
            return Command::FAILURE;
        }

        try {
            $this->syncRatings->execute($ratingsCsvPath);
        } catch (\Throwable $t) {
            $this->logger->error('Could not complete letterboxd sync.', ['exception' => $t]);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
