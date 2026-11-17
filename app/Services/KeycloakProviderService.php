<?php

namespace App\Services;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\User as SocialiteUser;

class KeycloakProviderService extends AbstractProvider implements ProviderInterface
{
    /**
     * Retrieve the authenticated user from Keycloak and return it as a SocialiteUser with tokens attached.
     *
     * Exchanges the authorization code for an access token, obtains user info using that token,
     * maps the info into a SocialiteUser, and attaches the access token plus any refresh token
     * and expiration returned by Keycloak.
     *
     * @return User The mapped SocialiteUser with `token`, optional `refreshToken`, and optional `expiresIn` set.
     *
     * @throws InvalidStateException If the OAuth state is invalid.
     */
    public function user(): SocialiteUser
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());
        $user = $this->getUserByToken($response['access_token']);

        $socialiteUser = $this->mapUserToObject($user);

        $socialiteUser->setToken($response['access_token']);
        if (isset($response['refresh_token'])) {
            $socialiteUser->setRefreshToken($response['refresh_token']);
        }
        if (isset($response['expires_in'])) {
            $socialiteUser->setExpiresIn($response['expires_in']);
        }

        return $socialiteUser;
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            config('services.keycloak.base_url').'/realms/daara/protocol/openid-connect/auth',
            $state
        );
    }

    protected function getTokenUrl(): string
    {
        return config('services.keycloak.base_url').'/realms/daara/protocol/openid-connect/token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            config('services.keycloak.base_url').'/realms/daara/protocol/openid-connect/userinfo',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user): SocialiteUser
    {
        return (new SocialiteUser)->setRaw($user)->map([
            'id' => $user['sub'] ?? $user['id'] ?? null,
            'nickname' => $user['preferred_username'] ?? null,
            'name' => $user['name'] ?? trim(($user['given_name'] ?? '').' '.($user['family_name'] ?? '')) ?: null,
            'email' => $user['email'] ?? null,
            'avatar' => $user['picture'] ?? null,
        ]);
    }

    protected function getTokenFields($code): array
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.keycloak.client_id'),
            'client_secret' => config('services.keycloak.client_secret'),
            'redirect_uri' => config('services.keycloak.redirect'),
            'code' => $code,
        ];
    }

    protected function getCodeFields($state = null): array
    {
        return array_merge(
            parent::getCodeFields($state),
            ['response_mode' => 'query']
        );
    }
}
