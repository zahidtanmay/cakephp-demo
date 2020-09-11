<?php

namespace App\Middleware;

use Authentication\Authenticator\UnauthenticatedException;
use Cake\Http\Cookie\Cookie;
use Cake\I18n\Time;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Cake\Http\Exception\UnauthorizedException;
use App\Model\Table\UsersTable;

class JwtMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $attribute = $request->getAttribute('params');
        if ($attribute['controller'] == 'Users' && $attribute['action'] == 'index') {

            $params = $request->getQueryParams();

            if (!isset($params['token'])) {
                throw new UnauthorizedException("Token not provided");
            }

            $token = $params['token'];

            try {
                $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
            } catch(ExpiredException $e) {
                throw new ExpiredException("Provided token has been expired");
            } catch(\Exception $e) {
                throw new UnauthorizedException("Invalid token provided");
            }

            $request->auth = $credentials->sub;
        }

        $response = $handler->handle($request);

        return $response;
    }
}