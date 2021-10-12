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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Security\DataAccess\Repository;

use common_exception_NoImplementation;
use OAT\Library\Lti1p3Core\Security\Key\Key;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use oat\oatbox\cache\SimpleCache;
use oat\oatbox\service\ConfigurableService;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class CachedPlatformKeyChainRepository extends ConfigurableService implements KeyChainRepositoryInterface
{
    public const PRIVATE_PATTERN = 'PLATFORM_LTI_PRIVATE_KEY_%s';
    public const PUBLIC_PATTERN = 'PLATFORM_LTI_PUBLIC_KEY_%s';

    public function save(KeyChainInterface $keyChain): bool
    {
        $this->setKeys(
            $keyChain,
            $keyChain->getIdentifier()
        );

        return $this->getPlatformKeyChainRepository()->save($keyChain);
    }

    public function find(string $identifier): ?KeyChainInterface
    {
        if ($this->isCacheAvailable($identifier)) {
            //TODO: Needs to be refactor if we have multiple key chains
            $rawKeys = $this->getCacheService()->getMultiple(
                [
                    sprintf(self::PRIVATE_PATTERN, $identifier),
                    sprintf(self::PUBLIC_PATTERN, $identifier),
                ]
            );

            $platformKeyChainRepository = $this->getPlatformKeyChainRepository();

            return new KeyChain(
                $platformKeyChainRepository->getOption(PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID),
                $platformKeyChainRepository->getOption(PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME),
                new Key($rawKeys[sprintf(self::PUBLIC_PATTERN, $identifier)]),
                new Key($rawKeys[sprintf(self::PRIVATE_PATTERN, $identifier)])
            );
        }

        $keyChain = $this->getPlatformKeyChainRepository()->find($identifier);

        if ($keyChain !== null) {
            $this->setKeys($keyChain, $identifier);
        }

        return $keyChain;
    }

    /**
     * @throws common_exception_NoImplementation
     */
    public function findByKeySetName(string $keySetName): array
    {
        throw new common_exception_NoImplementation();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setKeys(KeyChainInterface $keyChain, string $identifier): void
    {
        $this->getCacheService()->set(
            sprintf(self::PRIVATE_PATTERN, $identifier),

            $keyChain->getPrivateKey()->getContent()
        );

        $this->getCacheService()->set(
            sprintf(self::PUBLIC_PATTERN, $identifier),

            $keyChain->getPublicKey()->getContent()
        );
    }

    private function isCacheAvailable(string $identifier): bool
    {
        return $this->getCacheService()->has(sprintf(self::PRIVATE_PATTERN, $identifier)) &&
            $this->getCacheService()->has(sprintf(self::PUBLIC_PATTERN, $identifier));
    }

    private function getCacheService(): CacheInterface
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }

    private function getPlatformKeyChainRepository(): PlatformKeyChainRepository
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::SERVICE_ID);
    }
}
