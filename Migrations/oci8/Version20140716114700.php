<?php

namespace Claroline\CoreBundle\Migrations\oci8;

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
            DROP INDEX UNIQ_74DEBECDA76ED395
        ");
        $this->addSql("
            DROP INDEX UNIQ_74DEBECD9D070DE7
        ");
        $this->addSql("
            DROP INDEX UNIQ_74DEBECDA96EF72D
        ");
        $this->addSql("
            DROP INDEX UNIQ_74DEBECDA3C10BE
        ");
        $this->addSql("
            CREATE INDEX IDX_74DEBECDA76ED395 ON claro_mooc_sessions_by_users (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_74DEBECD9D070DE7 ON claro_mooc_sessions_by_users (moocSession_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_74DEBECDA96EF72D ON claro_mooc_sessions_by_users (moocOwner_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_74DEBECDA3C10BE ON claro_mooc_sessions_by_users (moocAccessConstraints_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_74DEBECDA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_74DEBECD9D070DE7
        ");
        $this->addSql("
            DROP INDEX IDX_74DEBECDA96EF72D
        ");
        $this->addSql("
            DROP INDEX IDX_74DEBECDA3C10BE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA76ED395 ON claro_mooc_sessions_by_users (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECD9D070DE7 ON claro_mooc_sessions_by_users (moocSession_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA96EF72D ON claro_mooc_sessions_by_users (moocOwner_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA3C10BE ON claro_mooc_sessions_by_users (moocAccessConstraints_id)
        ");
    }
}