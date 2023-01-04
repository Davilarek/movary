<?php declare(strict_types=1);

namespace Movary\Domain\User;

class UserEntity
{
    private function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $passwordHash,
        private readonly int $privacyLevel,
        private readonly bool $areCoreAccountChangesDisabled,
        private readonly int $dateFormatId,
        private readonly ?string $plexWebhookUuid,
        private readonly ?string $traktUserName,
        private readonly ?string $traktClientId,
        private readonly bool $jellyfinScrobbleWatches,
        private readonly bool $plexScrobbleViews,
        private readonly bool $plexScrobbleRating,
    ) {
    }

    public static function createFromArray(array $data) : self
    {
        return new self(
            (int)$data['id'],
            $data['name'],
            $data['password'],
            $data['privacy_level'],
            (bool)$data['core_account_changes_disabled'],
            $data['date_format_id'],
            $data['plex_webhook_uuid'],
            $data['trakt_user_name'],
            $data['trakt_client_id'],
            (bool)$data['jellyfin_scrobble_watches'],
            (bool)$data['plex_scrobble_views'],
            (bool)$data['plex_scrobble_ratings'],
        );
    }

    public function areCoreAccountChangesDisabled() : bool
    {
        return $this->areCoreAccountChangesDisabled;
    }

    public function getDateFormatId() : int
    {
        return $this->dateFormatId;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getJellyfinWebhookId() : ?string
    {
        return $this->plexWebhookUuid;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPasswordHash() : string
    {
        return $this->passwordHash;
    }

    public function getPlexScrobbleRating() : bool
    {
        return $this->plexScrobbleRating;
    }

    public function getPlexScrobbleViews() : bool
    {
        return $this->plexScrobbleViews;
    }

    public function getPlexWebhookId() : ?string
    {
        return $this->plexWebhookUuid;
    }

    public function getPrivacyLevel() : int
    {
        return $this->privacyLevel;
    }

    public function getTraktClientId() : ?string
    {
        return $this->traktClientId;
    }

    public function getTraktUserName() : ?string
    {
        return $this->traktUserName;
    }

    public function hasJellyfinScrobbleWatchesEnabled() : bool
    {
        return $this->jellyfinScrobbleWatches;
    }
}
