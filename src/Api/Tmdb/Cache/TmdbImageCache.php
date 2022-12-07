<?php declare(strict_types=1);

namespace Movary\Api\Tmdb\Cache;

use Movary\Api\Tmdb\TmdbUrlGenerator;
use Movary\Service\ImageCacheService;
use Movary\ValueObject\Job;
use PDO;
use Psr\Log\LoggerInterface;

class TmdbImageCache
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly ImageCacheService $imageCacheService,
        private readonly TmdbUrlGenerator $tmdbUrlGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return int Count of newly cached images
     */
    public function cacheAllMovieImages(bool $forceRefresh = false) : int
    {
        return $this->cacheImages('movie', $forceRefresh);
    }

    /**
     * @return int Count of newly cached images
     */
    public function cacheAllPersonImages(bool $forceRefresh = false) : int
    {
        return $this->cacheImages('person', $forceRefresh);
    }

    public function deleteCache() : void
    {
        $this->imageCacheService->deleteImages();
        $this->pdo->prepare('UPDATE movie SET poster_path = null')->execute();
        $this->pdo->prepare('UPDATE person SET poster_path = null')->execute();
    }

    public function executeJob(Job $job) : void
    {
        foreach ($job->getParameters()['movieIds'] ?? [] as $movieId) {
            $this->cacheAllImagesByMovieId($movieId);
        }

        $this->cachePersonImagesByIds($job->getParameters()['personIds']);
    }

    private function cacheAllImagesByMovieId(int $movieId) : void
    {
        $this->cacheImages('movie', false, [$movieId]);

        $statement = $this->pdo->prepare(
            "SELECT DISTINCT (id)
            FROM (
                SELECT id
                FROM person
                JOIN movie_cast cast on person.id = cast.person_id
                WHERE cast.movie_id = ?
                UNION
                SELECT id
                FROM person
                JOIN movie_crew crew on person.id = crew.person_id
                WHERE crew.movie_id = ?
            ) personIdTable",
        );
        $statement->execute([$movieId, $movieId]);

        $this->cacheImages('person', false, array_column($statement->fetchAll(), 'id'));
    }

    /**
     * @return bool True if image cache was re/generated, false otherwise
     */
    private function cacheImageDataByTableName(array $data, string $tableName, bool $forceRefresh = false) : bool
    {
        $cachedImagePublicPath = null;

        if ($data['tmdb_poster_path'] === null) {
            return false;
        }

        try {
            $cachedImagePublicPath = $this->imageCacheService->cacheImage(
                $this->tmdbUrlGenerator->generateImageUrl($data['tmdb_poster_path']),
                $data['poster_path'] === null ? true : $forceRefresh,
            );
        } catch (\Exception $e) {
            $this->logger->warning('Could not cache ' . $tableName . 'image: ' . $data['tmdb_poster_path'], ['exception' => $e]);
        }

        if ($cachedImagePublicPath === null) {
            return false;
        }

        $payload = [$cachedImagePublicPath, $data['id']];

        return $this->pdo->prepare("UPDATE $tableName SET poster_path = ? WHERE id = ?")->execute($payload);
    }

    /**
     * @return int Count of re/generated cached images
     */
    private function cacheImages(string $tableName, bool $forceRefresh, array $filerIds = []) : int
    {
        $cachedImages = 0;

        $query = "SELECT id, poster_path, tmdb_poster_path FROM $tableName";
        if (count($filerIds) > 0) {
            $placeholders = str_repeat('?, ', count($filerIds));
            $query .= ' WHERE id IN (' . trim($placeholders, ', ') . ')';
        }

        $statement = $this->pdo->prepare($query);
        $statement->execute($filerIds);

        foreach ($statement as $imageDataBeforeUpdate) {
            if ($this->cacheImageDataByTableName($imageDataBeforeUpdate, $tableName, $forceRefresh) === true) {
                if ($imageDataBeforeUpdate['poster_path'] !== null) {
                    $statement = $this->pdo->prepare("SELECT poster_path FROM $tableName WHERE id = ?");
                    $statement->execute([$imageDataBeforeUpdate['id']]);

                    $imageDataAfterUpdate = $statement->fetch();
                    if ($imageDataAfterUpdate['poster_path'] !== $imageDataBeforeUpdate['poster_path']) {
                        $this->imageCacheService->deleteImage($imageDataBeforeUpdate['poster_path']);
                    }
                }

                $cachedImages++;
            }
        }

        return $cachedImages;
    }

    private function cachePersonImagesByIds(array $personIds) : void
    {
        $this->cacheImages('person', false, $personIds);
    }
}
