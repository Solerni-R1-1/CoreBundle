<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/10 11:38:21
 */
class Version20140710113819 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_access_constraints (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                whitelist VARCHAR(MAX), 
                patterns VARCHAR(MAX), 
                moocOwner_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id) 
            WHERE moocOwner_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_users (
                moocaccessconstraints_id INT NOT NULL, 
                user_id INT NOT NULL, 
                PRIMARY KEY (
                    moocaccessconstraints_id, user_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8DECBE50DDA4386D ON claro_mooc_constraints_to_users (moocaccessconstraints_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8DECBE50A76ED395 ON claro_mooc_constraints_to_users (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            ADD CONSTRAINT FK_8C229ACDA96EF72D FOREIGN KEY (moocOwner_id) 
            REFERENCES claro_mooc_owner (id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_users 
            ADD CONSTRAINT FK_8DECBE50DDA4386D FOREIGN KEY (moocaccessconstraints_id) 
            REFERENCES claro_mooc_access_constraints (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_users 
            ADD CONSTRAINT FK_8DECBE50A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_owner ALTER COLUMN name NVARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_users 
            DROP CONSTRAINT FK_8DECBE50DDA4386D
        ");
        $this->addSql("
            DROP TABLE claro_mooc_access_constraints
        ");
        $this->addSql("
            DROP TABLE claro_mooc_constraints_to_users
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_owner ALTER COLUMN name NVARCHAR(255)
        ");
    }
}