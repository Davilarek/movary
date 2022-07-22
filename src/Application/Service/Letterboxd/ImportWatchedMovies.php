<?php declare(strict_types=1);

namespace Movary\Application\Service\Letterboxd;

use League\Csv\Reader;
use Movary\Api\Letterboxd\WebScrapper;
use Movary\Application\Movie;
use Movary\Application\Service\Letterboxd\ValueObject\CsvLineHistory;
use Movary\Application\Service\Tmdb;
use Psr\Log\LoggerInterface;

class ImportWatchedMovies
{
    public function __construct(
        private readonly Movie\Api $movieApi,
        private readonly WebScrapper $webScrapper,
        private readonly LoggerInterface $logger,
        private readonly Tmdb\SyncMovie $tmdbMovieSync
    ) {
    }

    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    public function execute(int $userId, string $historyCsvPath, bool $overwriteExistingData = false) : void
    {
        $watchDates = Reader::createFromPath($historyCsvPath);
        $watchDates->setHeaderOffset(0);

        foreach ($watchDates->getRecords() as $watchDate) {
            $csvLineHistory = CsvLineHistory::createFromCsvLine($watchDate);

            $movie = $this->fetchMovieByLetterboxdUri($csvLineHistory->getLetterboxdUri());

            if ($overwriteExistingData === false && $this->movieApi->findUserRating($movie->getId(), $userId) !== null) {
                $this->logger->info('Ignoring already existing watch date for movie: ' . $movie->getTitle());

                continue;
            }

            $this->movieApi->replaceHistoryForMovieByDate($movie->getId(), $userId, $csvLineHistory->getDate(), 1);
            $this->logger->info(sprintf('Imported watch date for movie "%s": %s', $csvLineHistory->getName(), $csvLineHistory->getDate()));
        }

        unlink($historyCsvPath);
    }

    public function fetchMovieByLetterboxdUri(string $letterboxdUri) : Movie\Entity
    {
        $letterboxdId = basename($letterboxdUri);
        $movie = $this->movieApi->findByLetterboxdId($letterboxdId);

        if ($movie === null) {
            $tmdbId = $this->webScrapper->getProviderTmdbId($letterboxdUri);

            $movie = $this->movieApi->findByTmdbId($tmdbId);

            if ($movie === null) {
                $movie = $this->tmdbMovieSync->syncMovie($tmdbId);
            }

            $this->movieApi->updateLetterboxdId($movie->getId(), $letterboxdId);
        }

        return $movie;
    }
}
