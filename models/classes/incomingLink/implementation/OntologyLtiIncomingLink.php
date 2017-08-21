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

use oat\taoLti\models\classes\incomingLink\LtiIncomingLink;


/**
 * Interface containing the Lti Role URIs
 */
class OntologyLtiIncomingLink extends \core_kernel_classes_Resource implements LtiIncomingLink
{
    const PROPERTY_LINK_DELIVERY = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LinkDelivery';

    public function __construct($uri, $debug = '')
    {
        parent::__construct($uri, $debug);
    }

    public function getDelivery()
    {
        return $this->getOnePropertyValue($this->getProperty(self::PROPERTY_LINK_DELIVERY));
    }

    public function getIdentifier()
    {
        return $this->getUri();
    }

    public function setDelivery(\core_kernel_classes_Resource $delivery)
    {
        return $this->editPropertyValues($this->getProperty(self::PROPERTY_LINK_DELIVERY), $delivery);
    }


}
