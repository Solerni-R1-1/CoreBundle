<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                moocSession_id INT DEFAULT NULL, 
                moocOwner_id INT DEFAULT NULL, 
                moocAccessConstraints_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_74DEBECDA76ED395 (user_id), 
                UNIQUE INDEX UNIQ_74DEBECD9D070DE7 (moocSession_id), 
                UNIQUE INDEX UNIQ_74DEBECDA96EF72D (moocOwner_id), 
                UNIQUE INDEX UNIQ_74DEBECDA3C10BE (moocAccessConstraints_id)
            )
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