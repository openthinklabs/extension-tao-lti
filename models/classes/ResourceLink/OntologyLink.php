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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\taoLti\models\classes\ResourceLink;

use oat\oatbox\service\ConfigurableService;
use oat\generis\model\OntologyAwareTrait;

/**
 * Service to generate unique ids for consumers resource links
 * using the generis ontology
 *
 * @author joel bout (joel@taotesting.com)
 */
class OntologyLink extends ConfigurableService implements LinkService
{
    use OntologyAwareTrait;

    public const CLASS_LTI_INCOMINGLINK = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiIncomingLink';

    public const PROPERTY_LINK_ID = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTILinkId';

    public const PROPERTY_CONSUMER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTILinkConsumer';

    public const PROPERTY_LAUNCH_URL = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#ResourceLinkLaunchUrl';

    public function getLinkId($consumerId, $resourceLink): string
    {
        $class = $this->getClass(self::CLASS_LTI_INCOMINGLINK);
        // search for existing resource
        $instances = $class->searchInstances([
            self::PROPERTY_LINK_ID => $resourceLink,
            self::PROPERTY_CONSUMER => $consumerId
        ], ['like' => false,'recursive' => false]);
        if (count($instances) > 1) {
            throw new \common_exception_Error('Multiple resources for link ' . $resourceLink);
        }
        if (count($instances) == 1) {
            // use existing link
            $ltiLink = current($instances);
        } else {
            // spawn new link
            $ltiLink = $class->createInstanceWithProperties([
                self::PROPERTY_LINK_ID => $resourceLink,
                self::PROPERTY_CONSUMER => $consumerId
            ]);
        }
        return $ltiLink->getUri();
    }
}
