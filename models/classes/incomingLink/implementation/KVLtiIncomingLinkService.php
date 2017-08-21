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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoLti\models\classes\incomingLink\implementation;

use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\incomingLink\LtiIncomingLink;
use oat\taoLti\models\classes\incomingLink\LtiIncomingLinkService;


/**
 * Interface containing the Lti Role URIs
 */
class KVLtiIncomingLinkService extends ConfigurableService implements LtiIncomingLinkService
{

    const OPTION_PERSISTENCE = 'persistence';

    const LTI_IL = 'kvil_';

    /**
     * @var \common_persistence_KeyValuePersistence
     */
    private $persistence;

    /**
     * @return \common_persistence_KeyValuePersistence|\common_persistence_Persistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)) {
            $persistenceOption = $this->getOption(self::OPTION_PERSISTENCE);
            $this->persistence = (is_object($persistenceOption))
                ? $persistenceOption
                : \common_persistence_KeyValuePersistence::getPersistence($persistenceOption);
        }
        return $this->persistence;
    }

    public function getLtiLink($consumer, $linkId)
    {

        $data = $this->getPersistence()->get(self::LTI_IL . $consumer . $linkId);

        return KVLtiIncomingLink::unSerialize($data);

    }

    public function spawnLtiLink($consumer, $linkId)
    {
        $incomingLink = new KVLtiIncomingLink($consumer, $linkId);
        $this->getPersistence()->set(self::LTI_IL . $consumer . $linkId, json_encode($incomingLink));

        return $incomingLink;
    }

    public function get($ltiLinkIdentifier)
    {
        $data = $this->getPersistence()->get(self::LTI_IL . $ltiLinkIdentifier);

        return KVLtiIncomingLink::unSerialize($data);
    }


}
