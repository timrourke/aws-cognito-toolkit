<?php

declare(strict_types=1);

namespace Incognito\FunctionalTests;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Incognito\CognitoClient\CognitoCredentials;
use Incognito\CognitoClient\UserAuthenticationService;
use Symfony\Component\Process\Process;

/**
 * Class UserAuthenticationServiceFactory
 *
 * Useful for instantiating a UserAuthenticationService instance for tests based
 * on the Terraform state generated by the automated provisioning
 *
 * @see \Incognito\FunctionalTests\TerraformExtension
 * @package Incognito\FunctionalTests
 */
class UserAuthenticationServiceFactory
{
    /**
     * @var \Incognito\CognitoClient\UserAuthenticationService
     */
    private static UserAuthenticationService $userAuthenticationService;

    private function __construct()
    {
    }

    public static function build(): UserAuthenticationService
    {
        if (isset(self::$userAuthenticationService)) {
            return self::$userAuthenticationService;
        }

        $instance = new self;

        return $instance->createUserAuthenticationService();
    }

    private function createUserAuthenticationService(): UserAuthenticationService
    {
        $client = $this->getCognitoClient();
        $credentials = $this->getCredentials();

        return new UserAuthenticationService($client, $credentials);
    }

    private function getCredentials(): CognitoCredentials
    {
        return new CognitoCredentials(
            $this->getTerraformOutput('aws_cognito_user_pool_client_id'),
            $this->getTerraformOutput('aws_cognito_user_pool_client_secret'),
            $this->getTerraformOutput('aws_cognito_user_pool_id')
        );
    }

    private function getTerraformOutput(string $key): string
    {
        $process = Process::fromShellCommandline(
            "terraform output $key",
            __DIR__ . '/terraform'
        );

        $process->mustRun();

        return trim($process->getOutput());
    }

    private function getCognitoClient(): CognitoIdentityProviderClient
    {
        return new CognitoIdentityProviderClient([
            'region' => 'us-east-1',
            'version' => 'latest',
        ]);
    }
}
