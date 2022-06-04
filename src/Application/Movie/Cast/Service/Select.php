<?php declare(strict_types=1);

namespace Movary\Application\Movie\Cast\Service;

use Movary\Application\Movie\Cast\Repository;
use Movary\Application\Person;

class Select
{
    public function __construct(private readonly Repository $repository, private readonly Person\Api $personApi)
    {
    }

    public function findByMovieId(int $movieId) : ?array
    {
        $castMembers = [];

        foreach ($this->repository->findByMovieId($movieId) as $movieGenre) {
            $person = $this->personApi->findById($movieGenre->getPersonId());

            $castMembers[] = [
                'id' => $person?->getId(),
                'name' => $person?->getName(),
                'tmdbPosterPath' => $person?->getTmdbPosterPath(),
            ];
        }

        return $castMembers;
    }
}
