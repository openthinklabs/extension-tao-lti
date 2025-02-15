<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use core_kernel_classes_Resource;
use core_kernel_users_Cache;
use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoLti\models\classes\LtiRoles;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202304261522423772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Apply permission for AuthoringTool';
    }

    public function up(Schema $schema): void
    {
        AclProxy::applyRule($this->getLaunchActionRule());
        AclProxy::applyRule($this->getRunActionRule(LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_DEVELOPER));
        AclProxy::applyRule($this->getRunActionRule(LtiRoles::CONTEXT_LTI1P3_ADMINISTRATOR_SUB_DEVELOPER));

        $this->addReport(
            Report::createInfo(
                sprintf(
                    'Clearing the Generis cache for roles %s and %s',
                    LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_DEVELOPER,
                    LtiRoles::CONTEXT_LTI1P3_ADMINISTRATOR_SUB_DEVELOPER
                )
            )
        );
        core_kernel_users_Cache::removeIncludedRoles(
            new core_kernel_classes_Resource(LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_DEVELOPER)
        );
        core_kernel_users_Cache::removeIncludedRoles(
            new core_kernel_classes_Resource(LtiRoles::CONTEXT_LTI1P3_ADMINISTRATOR_SUB_DEVELOPER)
        );

        OntologyUpdater::syncModels();

        $this->addReport(Report::createInfo('Apply permission for AuthoringTool'));
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->getLaunchActionRule());
        AclProxy::revokeRule($this->getRunActionRule(LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_DEVELOPER));
        AclProxy::revokeRule($this->getRunActionRule(LtiRoles::CONTEXT_LTI1P3_ADMINISTRATOR_SUB_DEVELOPER));

        $this->addReport(Report::createInfo('Revoke permission for AuthoringTool'));
    }

    private function getLaunchActionRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            TaoRoles::ANONYMOUS,
            ['ext' => 'taoLti', 'mod' => 'AuthoringTool', 'act' => 'launch']
        );
    }

    private function getRunActionRule(string $role): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            $role,
            ['ext' => 'taoLti', 'mod' => 'AuthoringTool', 'act' => 'run']
        );
    }
}
