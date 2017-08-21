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

namespace oat\taoLti\models\classes;


/**
 * Interface containing the Lti Role URIs
 */
class KVLtiIncomingLink implements LtiIncomingLink, \JsonSerializable
{
    private $delivery;
    private $identifier;

    /**
     * KVLtiIncomingLink constructor.
     * @param $delivery
     * @param $identifier
     */
    public function __construct($delivery, $identifier)
    {
        $this->delivery = $delivery;
        $this->identifier = $identifier;
    }


    public function getDelivery()
    {
        return $this->delivery;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setDelivery(\core_kernel_classes_Resource $delivery)
    {
        return $this->delivery;
    }


    public static function unSerialize($value)
    {
        $data = json_decode($value,true);
        if(!is_null($data) && isset($data['identifier']) && isset($data['delivery'])){
            return new self($data['delivery'], $data['identifier']);
        }

        return null;
    }

    function jsonSerialize()
    {
        return [
            'delivery' => $this->getDelivery(),
            'identifier' => $this->getIdentifier(),
        ];
    }


}
