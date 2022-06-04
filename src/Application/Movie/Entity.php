<?php declare(strict_types=1);

namespace Movary\Application\Movie;

use Movary\Api\Trakt\ValueObject\Movie\TraktId;
use Movary\ValueObject\DateTime;

class Entity
{
    private function __construct(
        private readonly int $id,
        private readonly string $title,
        private readonly ?int $rating10,
        private readonly ?int $rating5,
        private readonly TraktId $traktId,
        private readonly string $imdbId,
        private readonly int $tmdbId,
        private readonly ?string $posterPath,
        private readonly ?string $overview,
        private readonly ?string $tagline,
        private readonly ?string $originalLanguage,
        private readonly ?int $runtime,
        private readonly ?DateTime $releaseDate,
        private readonly ?float $tmdbVoteAverage,
        private readonly ?int $tmdbVoteCount,
        private readonly ?string $tmdbPosterPath,
        private readonly ?DateTime $updatedAtTmdb
    ) {
    }

    public static function createFromArray(array $data) : self
    {
        return new self(
            (int)$data['id'],
            $data['title'],
            $data['rating_10'] === null ? null : (int)$data['rating_10'],
            $data['rating_5'] === null ? null : (int)$data['rating_5'],
            TraktId::createFromString((string)$data['trakt_id']),
            $data['imdb_id'],
            (int)$data['tmdb_id'],
            $data['poster_path'],
            $data['overview'],
            $data['tagline'],
            $data['original_language'],
            $data['runtime'] === null ? null : (int)$data['runtime'],
            $data['release_date'] === null ? null : DateTime::createFromString($data['release_date']),
            $data['tmdb_vote_average'] === null ? null : (float)$data['tmdb_vote_average'],
            $data['tmdb_vote_count'] === null ? null : (int)$data['tmdb_vote_count'],
            $data['tmdb_poster_path'],
            $data['updated_at_tmdb'] === null ? null : DateTime::createFromString($data['updated_at_tmdb']),
        );
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getImdbId() : string
    {
        return $this->imdbId;
    }

    public function getOriginalLanguage() : ?string
    {
        return $this->originalLanguage;
    }

    public function getOverview() : ?string
    {
        return $this->overview;
    }

    public function getPosterPath() : ?string
    {
        return $this->posterPath;
    }

    public function getRating10() : ?int
    {
        return $this->rating10;
    }

    public function getRating5() : ?int
    {
        return $this->rating5;
    }

    public function getReleaseDate() : ?DateTime
    {
        return $this->releaseDate;
    }

    public function getRuntime() : ?int
    {
        return $this->runtime;
    }

    public function getTagline() : ?string
    {
        return $this->tagline;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getTmdbId() : int
    {
        return $this->tmdbId;
    }

    public function getTmdbPosterPath() : ?string
    {
        return $this->tmdbPosterPath;
    }

    public function getTmdbVoteAverage() : ?float
    {
        return $this->tmdbVoteAverage;
    }

    public function getTmdbVoteCount() : ?int
    {
        return $this->tmdbVoteCount;
    }

    public function getTraktId() : TraktId
    {
        return $this->traktId;
    }

    public function getUpdatedAtTmdb() : ?DateTime
    {
        return $this->updatedAtTmdb;
    }
}
