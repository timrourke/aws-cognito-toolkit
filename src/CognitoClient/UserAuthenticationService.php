<?php

declare(strict_types=1);

namespace Incognito\CognitoClient;

use Aws\Exception\AwsException;
use Aws\Result;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient as CognitoClient;
use Incognito\CognitoClient\Exception\NotAuthorizedException;
use Incognito\CognitoClient\Exception\UserNotConfirmedException;
use Incognito\CognitoClient\Exception\UserNotFoundException;
use Incognito\Entity\User;
use Incognito\Entity\UserAttribute\UserAttribute;

class UserAuthenticationService
{
    /**
     * @var \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient
     */
    private $cognitoClient;

    /**
     * @var \Incognito\CognitoClient\CognitoCredentials
     */
    private $cognitoCredentials;

    /**
     * Constructor.
     *
     * @param \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient $cognitoClient
     * @param \Incognito\CognitoClient\CognitoCredentials $cognitoCredentials
     */
    public function __construct(
        CognitoClient $cognitoClient,
        CognitoCredentials $cognitoCredentials
    ) {
        $this->cognitoClient      = $cognitoClient;
        $this->cognitoCredentials = $cognitoCredentials;
    }

    /**
     * Log a user in via username and password
     *
     * @param string $username
     * @param string $password
     * @return \Aws\Result
     * @throws \Incognito\CognitoClient\Exception\UserNotConfirmedException
     * @throws \Exception
     */
    public function loginUser(string $username, string $password): Result
    {
        $result = null;

        try {
            $result = $this->cognitoClient->adminInitiateAuth([
                'AuthFlow'       => 'ADMIN_NO_SRP_AUTH',
                'ClientId'       => $this->cognitoCredentials->getClientId(),
                'UserPoolId'     => $this->cognitoCredentials->getUserPoolId(),
                'AuthParameters' => [
                    'SECRET_HASH' => $this->cognitoCredentials->getSecretHashForUsername(
                        $username
                    ),
                    'USERNAME'    => $username,
                    'PASSWORD'    => $password,
                ],
            ]);
        } catch(AwsException $e) {
            $this->handleLoginAwsException($e);
        }

        return $result;
    }

    /**
     * Refresh the current AWS Cognito session and return a new access token
     *
     * @param string $username
     * @param string $refreshToken
     * @return \Aws\Result
     */
    public function refreshToken(string $username, string $refreshToken): Result
    {
         return $this->cognitoClient->adminInitiateAuth([
              'AuthFlow'       => 'REFRESH_TOKEN',
              'ClientId'       => $this->cognitoCredentials->getClientId(),
              'UserPoolId'     => $this->cognitoCredentials->getUserPoolId(),
              'AuthParameters' => [
                  'REFRESH_TOKEN' => $refreshToken,
                  'SECRET_HASH' => $this->cognitoCredentials->getSecretHashForUsername(
                      $username
                  ),
              ],
         ]);
    }

    /**
     * Sign up a new user
     *
     * @param \Incognito\Entity\User $user
     * @param string $password
     * @return \Aws\Result
     */
    public function signUpUser(User $user, string $password): Result
    {
        return $this->cognitoClient->signUp([
            'ClientId'   => $this->cognitoCredentials->getClientId(),
            'Password'   => $password,
            'SecretHash' => $this->cognitoCredentials->getSecretHashForUsername(
                $user->username()
            ),
            'UserAttributes' => array_map(
                function(UserAttribute $attr) {
                    return [
                        'Name' => $attr->name(),
                        'Value' => $attr->value(),
                    ];
                },
                $user->getAttributes()
            ),
            'Username' => $user->username(),
        ]);
    }

    /**
     * @param \Aws\Exception\AwsException $e
     * @throws \Incognito\CognitoClient\Exception\NotAuthorizedException
     * @throws \Incognito\CognitoClient\Exception\UserNotConfirmedException
     * @throws \Incognito\CognitoClient\Exception\UserNotFoundException
     */
    private function handleLoginAwsException(AwsException $e): void
    {
        switch ($e->getAwsErrorCode()) {
            case 'NotAuthorizedException':
                throw new NotAuthorizedException($e);
            case 'UserNotConfirmedException':
                throw new UserNotConfirmedException($e);
            case 'UserNotFoundException':
                throw new UserNotFoundException($e);
            default:
                throw $e;
        }
    }
}
