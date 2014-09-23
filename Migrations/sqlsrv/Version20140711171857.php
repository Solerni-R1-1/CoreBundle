<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/11 05:18:59
 */
class Version20140711171857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_moocs (
                mooc_id INT NOT NULL, 
                moocaccessconstraints_id INT NOT NULL, 
                PRIMARY KEY (
                    mooc_id, moocaccessconstraints_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_99FD2CF2255EEB87 ON claro_mooc_constraints_to_moocs (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_99FD2CF2DDA4386D ON claro_mooc_constraints_to_moocs (moocaccessconstraints_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_owner (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX), 
                logo_path NVARCHAR(255), 
                dressing_path NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
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
            CREATE INDEX IDX_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            ADD CONSTRAINT FK_99FD2CF2255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            ADD CONSTRAINT FK_99FD2CF2DDA4386D FOREIGN KEY (moocaccessconstraints_id) 
            REFERENCES claro_mooc_access_constraints (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            ADD CONSTRAINT FK_8C229ACDA96EF72D FOREIGN KEY (moocOwner_id) 
            REFERENCES claro_mooc_owner (id)
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = '[primary]'
            ) 
            ALTER TABLE claro_user_mooc_session 
            DROP CONSTRAINT [primary] ELSE 
            DROP INDEX [primary] ON claro_user_mooc_session
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD PRIMARY KEY (moocsession_id, user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD owner_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD CONSTRAINT FK_FB43C54E7E3C61F9 FOREIGN KEY (owner_id) 
            REFERENCES claro_mooc_owner (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FB43C54E7E3C61F9 ON claro_mooc (owner_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP CONSTRAINT FK_FB43C54E7E3C61F9
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            DROP CONSTRAINT FK_8C229ACDA96EF72D
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            DROP CONSTRAINT FK_99FD2CF2DDA4386D
        ");
        $this->addSql("
            DROP TABLE claro_mooc_constraints_to_moocs
        ");
        $this->addSql("
            DROP TABLE claro_mooc_owner
        ");
        $this->addSql("
            DROP TABLE claro_mooc_access_constraints
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN owner_id
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_FB43C54E7E3C61F9'
            ) 
            ALTER TABLE claro_mooc 
            DROP CONSTRAINT IDX_FB43C54E7E3C61F9 ELSE 
            DROP INDEX IDX_FB43C54E7E3C61F9 ON claro_mooc
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = '[PRIMARY]'
            ) 
            ALTER TABLE claro_user_mooc_session 
            DROP CONSTRAINT [PRIMARY] ELSE 
            DROP INDEX [PRIMARY] ON claro_user_mooc_session
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD PRIMARY KEY (user_id, moocsession_id)
        ");
    }
}