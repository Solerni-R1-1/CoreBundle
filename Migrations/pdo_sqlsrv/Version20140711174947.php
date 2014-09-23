<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/11 05:49:49
 */
class Version20140711174947 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_sessions_by_users (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                moocSession_id INT, 
                moocOwner_id INT, 
                moocAccessConstraints_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA76ED395 ON claro_mooc_sessions_by_users (user_id) 
            WHERE user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECD9D070DE7 ON claro_mooc_sessions_by_users (moocSession_id) 
            WHERE moocSession_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA96EF72D ON claro_mooc_sessions_by_users (moocOwner_id) 
            WHERE moocOwner_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA3C10BE ON claro_mooc_sessions_by_users (moocAccessConstraints_id) 
            WHERE moocAccessConstraints_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            ADD CONSTRAINT FK_74DEBECDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            ADD CONSTRAINT FK_74DEBECD9D070DE7 FOREIGN KEY (moocSession_id) 
            REFERENCES claro_mooc_session (id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            ADD CONSTRAINT FK_74DEBECDA96EF72D FOREIGN KEY (moocOwner_id) 
            REFERENCES claro_mooc_owner (id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            ADD CONSTRAINT FK_74DEBECDA3C10BE FOREIGN KEY (moocAccessConstraints_id) 
            REFERENCES claro_mooc_access_constraints (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_mooc_sessions_by_users
        ");
    }
}