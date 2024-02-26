<?php declare(strict_types=1);

namespace Movary\HttpController\Api\Middleware;

use Movary\Domain\User\UserApi;
use Movary\HttpController\Web\Middleware\MiddlewareInterface;
use Movary\ValueObject\Http\Response;

readonly class CreateUserMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserApi $userApi,
        private bool $registrationEnabled
    ) {
    }

    public function __invoke() : ?Response
    {
        if ($this->registrationEnabled === false && $this->userApi->hasUsers() === true) {
            return Response::createForbidden();
        }

        return null;
    }
}
