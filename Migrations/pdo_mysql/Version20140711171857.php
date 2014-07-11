<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

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
                INDEX IDX_99FD2CF2255EEB87 (mooc_id), 
                INDEX IDX_99FD2CF2DDA4386D (moocaccessconstraints_id), 
                PRIMARY KEY(
                    mooc_id, moocaccessconstraints_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_owner (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                logo_path VARCHAR(255) DEFAULT NULL, 
                dressing_path VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_access_constraints (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                whitelist LONGTEXT DEFAULT NULL, 
                patterns LONGTEXT DEFAULT NULL, 
                moocOwner_id INT DEFAULT NULL, 
                INDEX IDX_8C229ACDA96EF72D (moocOwner_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            ALTER TABLE claro_user_mooc_session 
            DROP PRIMARY KEY
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
            DROP FOREIGN KEY FK_FB43C54E7E3C61F9
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            DROP FOREIGN KEY FK_8C229ACDA96EF72D
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            DROP FOREIGN KEY FK_99FD2CF2DDA4386D
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
            DROP INDEX IDX_FB43C54E7E3C61F9 ON claro_mooc
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP owner_id
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            DROP PRIMARY KEY
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD PRIMARY KEY (user_id, moocsession_id)
        ");
    }
}