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
class OntologyLtiIncomingLinkService extends ConfigurableService implements LtiIncomingLinkService
{

    const CLASS_LTI_INCOMINGLINK = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiIncomingLink';

    const PROPERTY_LTI_LINK_ID = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTILinkId';

    const PROPERTY_LTI_LINK_CONSUMER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTILinkConsumer';


    public function getLtiLink($consumer, $linkId)
    {
        $class = new \core_kernel_classes_Class(self::CLASS_LTI_INCOMINGLINK);

        // search for existing resource
        $instances = $class->searchInstances(array(
            self::PROPERTY_LTI_LINK_ID => $linkId,
            self::PROPERTY_LTI_LINK_CONSUMER => $consumer
        ), array(
            'like' => false,
            'recursive' => false
        ));

        if (count($instances) > 1) {
            throw new \common_exception_Error('Multiple resources for link ' . $linkId);
        }

        if(empty($instances)){
            return null;
        }

        $instance = current($instances);
        $incomingLink = new OntologyLtiIncomingLink($instance->getUri());

        return $incomingLink;
    }

    public function spawnLtiLink($consumer, $linkId)
    {

        $class = new \core_kernel_classes_Class(self::CLASS_LTI_INCOMINGLINK);

        $instance = $class->createInstanceWithProperties(array(
            self::PROPERTY_LTI_LINK_ID		=> $linkId,
            self::PROPERTY_LTI_LINK_CONSUMER	=> $consumer,
        ));

        $incomingLink = new OntologyLtiIncomingLink($instance->getUri());

        return $incomingLink;
    }

    public function get($ltiLinkIdentifier)
    {
        $incomingLink = new OntologyLtiIncomingLink($ltiLinkIdentifier);

        return $incomingLink;
    }


}
