<?php declare(strict_types=1);

namespace Movary\HttpController;

use Movary\Api\Plex\PlexApi;
use Movary\Domain\User\Service\Authentication;
use Movary\Domain\User\UserApi;
use Movary\Service\Plex\PlexScrobbler;
use Movary\Util\Json;
use Movary\ValueObject\Http\Request;
use Movary\ValueObject\Http\Response;
use Psr\Log\LoggerInterface;

class PlexController
{
    public function __construct(
        private readonly Authentication $authenticationService,
        private readonly UserApi $userApi,
        private readonly PlexApi $plexApi,
        private readonly PlexScrobbler $plexScrobbler,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function deletePlexWebhookId() : Response
    {
        if ($this->authenticationService->isUserAuthenticated() === false) {
            return Response::createSeeOther('/');
        }

        $this->userApi->deletePlexWebhookId($this->authenticationService->getCurrentUserId());

        return Response::createOk();
    }

    public function getPlexWebhookId() : Response
    {
        if ($this->authenticationService->isUserAuthenticated() === false) {
            return Response::createSeeOther('/');
        }

        $plexWebhookId = $this->userApi->findPlexWebhookId($_SESSION['userId']);

        return Response::createJson(Json::encode(['id' => $plexWebhookId]));
    }

    public function handlePlexWebhook(Request $request) : Response
    {
        $webhookId = $request->getRouteParameters()['id'];

        $userId = $this->userApi->findUserIdByPlexWebhookId($webhookId);
        if ($userId === null) {
            return Response::createNotFound();
        }

        $requestPayload = $request->getPostParameters()['payload'] ?? null;
        if ($requestPayload === null) {
            return Response::createOk();
        }

        $this->logger->debug('Plex: Webhook triggered with payload: ' . $requestPayload);

        $this->plexScrobbler->processPlexWebhook($userId, Json::decode((string)$requestPayload));

        return Response::createOk();
    }

    public function regeneratePlexWebhookId() : Response
    {
        if ($this->authenticationService->isUserAuthenticated() === false) {
            return Response::createSeeOther('/');
        }

        $plexWebhookId = $this->userApi->regeneratePlexWebhookId($this->authenticationService->getCurrentUserId());

        return Response::createJson(Json::encode(['id' => $plexWebhookId]));
    }
    
    public function processPlexCallback() : Response
    {
        if ($this->authenticationService->isUserAuthenticated() === false) {
            return Response::createSeeOther('/');
        }

        $plexClientId = $this->userApi->findPlexClientId($this->authenticationService->getCurrentUserId());
        $plexClientCode = $this->userApi->findTemporaryPlexCode($this->authenticationService->getCurrentUserId());
        if($plexClientId === null || $plexClientCode === null) {
            return Response::createSeeOther('/');
        }
        
        $plexAccessToken = $this->plexApi->fetchPlexAccessToken($plexClientId, $plexClientCode);
        if($plexAccessToken === null) {
            return Response::createSeeOther('/');
        }
        $this->userApi->updatePlexAccessToken($this->authenticationService->getCurrentUserId(), $plexAccessToken->getPlexAccessTokenAsString());

        return Response::createSeeOther('/settings/plex');
    }
}
