<?php declare(strict_types=1);

namespace Movary\Service\Plex;

use Movary\Api\Plex\Dto\PlexItem;
use Movary\Api\Plex\Dto\PlexItemList;
use Movary\Api\Plex\PlexApi;
use Psr\Log\LoggerInterface;
use Movary\Domain\Movie\MovieApi;
use Movary\Domain\User\UserApi;
use Movary\Service\Tmdb\SyncMovie;
use Movary\Util\Json;

class PlexHistoryImporter
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MovieApi $movieApi,
        private readonly UserApi $userApi,
        private readonly SyncMovie $tmdbMovieSyncService,
        private readonly PlexApi $plexApi
    ) {
    }

    public function importPlexData(int $userId) : string
    {
        $unknownPlexItems = PlexItemList::create();
        $plexLibraries = $this->plexApi->fetchPlexLibraries();
        foreach($plexLibraries as $library) {
            if($library['type'] !== 'movie') {
                continue;
            }
            $libraryKey = (int)$library['key'];
            $libraryWatchHistory = $this->plexApi->fetchPlexLibraryWatchedHistory($libraryKey);
            if($libraryWatchHistory === null) {
                continue;
            }
            foreach($libraryWatchHistory as $watchedItem) {
                $key = (int)$watchedItem['ratingKey'];
                $plexItem = $this->plexApi->fetchPlexItem($key);
                if($plexItem->getTmdbId() === null) {
                    $unknownPlexItems->add($plexItem);
                }
                $this->importPlexMovie($plexItem, $userId);
            }
        }
        return Json::encode($unknownPlexItems);
    }

    private function importPlexMovie(PlexItem $plexItem, int $userId) : void
    {
        $historyEntry = $this->movieApi->findHistoryEntryForMovieByUserOnDate($plexItem->getTmdbId(), $userId, $plexItem->getLastViewedAt());
        if ($historyEntry !== null) {
            $this->logger->info('Netflix: Movie watch date ignored because it was already set.', [
                'movieId' => $plexItem->getTmdbId(),
                'movieTitle' => $plexItem->getTitle(),
                'watchDate' => $plexItem->getLastViewedAt(),
                'personalRating' => $plexItem->getUserRating(),
            ]);

            return;
        }

        $this->movieApi->increaseHistoryPlaysForMovieOnDate($plexItem->getTmdbId(), $userId, $plexItem->getLastViewedAt());
        $this->movieApi->updateUserRating($plexItem->getTmdbId(), $userId, $plexItem->getUserRating());

        $this->logger->info('Netflix: Movie watch date imported', [
            'movieId' => $plexItem->getTmdbId(),
            'moveTitle' => $plexItem->getTitle(),
            'watchDate' => $plexItem->getLastViewedAt(),
            'personalRating' => $plexItem->getUserRating(),
        ]);
    }
}