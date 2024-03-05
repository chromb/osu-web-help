<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Http\Controllers\Passport;

use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Exceptions\AuthenticationException;
use Laravel\Passport\Exceptions\OAuthServerException;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Extension of Laravel\Passport\Http\Controllers\AuthorizationController
 * to add support for scope normalization when requesting token scopes.
 */
class AuthorizationController extends PassportAuthorizationController
{
    /**
     * Authorize a client to access the user's account.
     * This overrides the default implementation to normalize scope requests
     * and sort the scopes by key order.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $psrRequest
     * @param  \Illuminate\Http\Request $request
     * @param  \Laravel\Passport\ClientRepository $clients
     * @param  \Laravel\Passport\TokenRepository $tokens
     * @return \Illuminate\Http\Response
     */
    public function authorize(
        ServerRequestInterface $psrRequest,
        Request $request,
        ClientRepository $clients,
        TokenRepository $tokens
    ) {
        try {
            return parent::authorize($this->normalizeRequestScopes($psrRequest), $request, $clients, $tokens);
        } catch (AuthenticationException $_e) {
            $cancelUrl = $request->fullUrl();
            $cancelUrl .= strpos($cancelUrl, '?') === false ? '?' : '&';
            $cancelUrl .= 'prompt=none';

            return ext_view('sessions.create', [
                'cancelUrl' => $cancelUrl,
            ]);
        } catch (OAuthServerException $e) {
            return $e->render($request);
        }
    }

    /**
     * Normalizes the authorization request's scopes.
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    private function normalizeRequestScopes(ServerRequestInterface $request): ServerRequestInterface
    {
        $params = $request->getQueryParams();
        $scopes = $this->normalizeScopes(
            explode(' ', $params['scope'] ?? '')
        );

        // temporary non-persisted token to validate with.
        $token = Passport::token()->forceFill([
            'client_id' => $params['client_id'] ?? null,
            'revoked' => false,
            'scopes' => $scopes,
        ]);
        $token->user()->associate(auth()->user());
        $token->validate();

        $params['scope'] = implode(' ', $scopes);

        return $request->withQueryParams($params);
    }

    /**
     * Normalizes and sorts scopes.
     *
     * @param array $scopes
     * @return array
     */
    private function normalizeScopes(array $scopes): array
    {
        if (!in_array('identify', $scopes, true)) {
            $scopes[] = 'identify';
        }

        sort($scopes);

        return $scopes;
    }
}
