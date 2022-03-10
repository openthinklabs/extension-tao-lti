<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\ServiceProvider;

use GuzzleHttp\ClientInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\generis\model\DependencyInjection\ServiceOptions;
use oat\generis\persistence\PersistenceManager;
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory;
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactoryInterface;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Jwks\Fetcher\JwksFetcher;
use OAT\Library\Lti1p3Core\Security\Jwks\Fetcher\JwksFetcherInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Entity\Scope;
use OAT\Library\Lti1p3Core\Security\OAuth2\Factory\AuthorizationServerFactory;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\AccessTokenRepository;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\ClientRepository;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\ScopeRepository;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use oat\oatbox\cache\factory\CacheItemPoolFactory;
use oat\oatbox\cache\ItemPoolSimpleCacheAdapter;
use oat\oatbox\log\LoggerService;
use oat\taoLti\models\classes\Client\LtiClientFactory;
use oat\taoLti\models\classes\LtiAgs\LtiAgsScoreService;
use oat\taoLti\models\classes\LtiAgs\LtiAgsScoreServiceInterface;
use oat\taoLti\models\classes\Platform\Repository\DefaultToolConfig;
use oat\taoLti\models\classes\Platform\Repository\DefaultToolConfigFactory;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationSnapshotRepository;
use oat\taoLti\models\classes\Platform\Repository\LtiPlatformFactory;
use oat\taoLti\models\classes\Platform\Service\UpdatePlatformRegistrationSnapshotListener;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

class LtiServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $parameters->set(
            'defaultScope',
            $_ENV['LTI_DEFAULT_SCOPE'] ?? 'https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome'
        );

        $services
            ->set(LtiClientFactory::class)
            ->args(
                [
                    service(ServiceOptions::class),
                ]
            );

        $services
            ->set(JwksFetcherInterface::class, JwksFetcher::class)
            ->public()
            ->args(
                [
                    service(ItemPoolSimpleCacheAdapter::class),
                    null,
                    null,
                    service(LoggerService::SERVICE_ID),
                ]
            );

        $services
            ->set(ClientRepositoryInterface::class, ClientRepository::class)
            ->public()
            ->args(
                [
                    service(Lti1p3RegistrationRepository::class),
                    service(JwksFetcherInterface::class),
                    service(LoggerService::SERVICE_ID),
                ]
            );

        $services
            ->set(AccessTokenRepositoryInterface::class, AccessTokenRepository::class)
            ->public()
            ->args(
                [
                    service(ItemPoolSimpleCacheAdapter::class),
                    service(LoggerService::SERVICE_ID),
                ]
            );

        $services
            ->set(ScopeEntityInterface::class, Scope::class)
            ->public()
            ->args(
                [
                    param('defaultScope'),
                ]
            );

        $services
            ->set(ScopeRepositoryInterface::class, ScopeRepository::class)
            ->public()
            ->args(
                [
                    [service(ScopeEntityInterface::class)],
                ]
            );

        $services
            ->set(AuthorizationServerFactory::class, AuthorizationServerFactory::class)
            ->public()
            ->args(
                [
                    service(ClientRepositoryInterface::class),
                    service(AccessTokenRepositoryInterface::class),
                    service(ScopeRepositoryInterface::class),
                    env('LTI_AUTHORIZATION_SERVER_FACTORY_ENCRYPTION_KEY'),
                ]
            );

        $services
            ->set(LtiServiceClientInterface::class, LtiServiceClient::class)
            ->args(
                [
                    inline_service(CacheItemPoolInterface::class)
                        ->factory([service(CacheItemPoolFactory::class), 'create'])
                        ->args([[]]),
                    inline_service(ClientInterface::class)
                        ->factory([service(LtiClientFactory::class), 'create']),
                ]
            );

        $services
            ->set(ScoreServiceInterface::class, ScoreServiceClient::class)
            ->public()
            ->args(
                [
                    service(LtiServiceClientInterface::class),
                ]
            );

        $services
            ->set(ScoreFactoryInterface::class, ScoreFactory::class)
            ->public();

        $services
            ->set(LtiAgsScoreServiceInterface::class, LtiAgsScoreService::class)
            ->public()
            ->args(
                [
                    service(ScoreServiceInterface::class),
                    service(ScoreFactoryInterface::class),
                ]
            );

        $services
            ->set(RegistrationRepositoryInterface::class, Lti1p3RegistrationSnapshotRepository::class)
            ->public()
            ->args(
                [
                    service(PersistenceManager::SERVICE_ID),
                    service(CachedPlatformKeyChainRepository::class),
                    service(PlatformKeyChainRepository::class),
                    inline_service(DefaultToolConfig::class)->arg('$baseUri', ROOT_URL),
                    'default'
                ]
            );

        $services
            ->set(UpdatePlatformRegistrationSnapshotListener::class, UpdatePlatformRegistrationSnapshotListener::class)
            ->public()
            ->args(
                [
                    service(RegistrationRepositoryInterface::class),
                    service(LtiPlatformFactory::class)
                ]
            );
    }
}
