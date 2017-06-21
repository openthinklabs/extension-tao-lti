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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoLti\helpers;

/**
 * Utility class to provide helpful parsing of request headers
 */
class HeaderParser
{

    /**
     * Gets the session value from the 'Cookie' header
     * @param {String} $header
     * @return {String}
     */
    public static function getSessionFromCookie($header)
    {
        // Parse header into assoc. array
        $cookies = [];
        if ( ! empty($header) ) {
            foreach (explode(';', $header) as $cookie) {
                $keyValue = explode('=', $cookie);
                $cookies[$keyValue[0]] = $keyValue[1];
            }
        }

        // Set session variable
        $session = null;
        if ( array_key_exists(GENERIS_SESSION_NAME, $cookies) ) {
            $session = $cookies[GENERIS_SESSION_NAME];
        }

        return $session;
    }

}