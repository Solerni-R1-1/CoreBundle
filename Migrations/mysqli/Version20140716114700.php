<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/16 11:47:02
 */
class Version20140716114700 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP INDEX UNIQ_74DEBECDA76ED395, 
            ADD INDEX IDX_74DEBECDA76ED395 (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP INDEX UNIQ_74DEBECD9D070DE7, 
            ADD INDEX IDX_74DEBECD9D070DE7 (moocSession_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP INDEX UNIQ_74DEBECDA96EF72D, 
            ADD INDEX IDX_74DEBECDA96EF72D (moocOwner_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP INDEX UNIQ_74DEBECDA3C10BE, 
            ADD INDEX IDX_74DEBECDA3C10BE (moocAccessConstraints_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP INDEX IDX_74DEBECDA76ED395, 
            ADD UNIQUE INDEX UNIQ_74DEBECDA76ED395 (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP INDEX IDX_74DEBECD9D070DE7, 
            ADD UNIQUE INDEX UNIQ_74DEBECD9D070DE7 (moocSession_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP INDEX IDX_74DEBECDA96EF72D, 
            ADD UNIQUE INDEX UNIQ_74DEBECDA96EF72D (moocOwner_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP INDEX IDX_74DEBECDA3C10BE, 
            ADD UNIQUE INDEX UNIQ_74DEBECDA3C10BE (moocAccessConstraints_id)
        ");
    }
}