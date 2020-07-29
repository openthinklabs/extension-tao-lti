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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\platform\builder;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\taoLti\models\tool\launch\LtiLaunchInterface;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;

class Lti1p1LaunchBuilder extends ConfigurableService implements LtiLaunchBuilderInterface
{
    public function withProvider(LtiProvider $ltiProvider): LtiLaunchBuilderInterface
    {
    }

    public function withUser(User $user): LtiLaunchBuilderInterface
    {
    }

    public function withRoles(array $roles): LtiLaunchBuilderInterface
    {
    }

    public function withClaims(array $claims): LtiLaunchBuilderInterface
    {
    }

    public function build(): LtiLaunchInterface
    {
    }
}
