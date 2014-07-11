<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/11 05:18:58
 */
class Version20140711171857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_moocs (
                mooc_id INT NOT NULL, 
                moocaccessconstraints_id INT NOT NULL, 
                PRIMARY KEY(
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
                id SERIAL NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description TEXT DEFAULT NULL, 
                logo_path VARCHAR(255) DEFAULT NULL, 
                dressing_path VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_access_constraints (
                id SERIAL NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                whitelist TEXT DEFAULT NULL, 
                patterns TEXT DEFAULT NULL, 
                moocOwner_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            ADD CONSTRAINT FK_99FD2CF2255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            ADD CONSTRAINT FK_99FD2CF2DDA4386D FOREIGN KEY (moocaccessconstraints_id) 
            REFERENCES claro_mooc_access_constraints (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            ADD CONSTRAINT FK_8C229ACDA96EF72D FOREIGN KEY (moocOwner_id) 
            REFERENCES claro_mooc_owner (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            DROP INDEX \"primary\"
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD PRIMARY KEY (moocsession_id, user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD owner_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD CONSTRAINT FK_FB43C54E7E3C61F9 FOREIGN KEY (owner_id) 
            REFERENCES claro_mooc_owner (id) NOT DEFERRABLE INITIALLY IMMEDIATE
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
            DROP INDEX IDX_FB43C54E7E3C61F9
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP owner_id
        ");
        $this->addSql("
            DROP INDEX \"PRIMARY\"
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD PRIMARY KEY (user_id, moocsession_id)
        ");
    }
}