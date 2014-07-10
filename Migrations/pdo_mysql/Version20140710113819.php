<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/10 11:38:20
 */
class Version20140710113819 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_access_constraints (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                whitelist LONGTEXT DEFAULT NULL, 
                patterns LONGTEXT DEFAULT NULL, 
                moocOwner_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_8C229ACDA96EF72D (moocOwner_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_users (
                moocaccessconstraints_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_8DECBE50DDA4386D (moocaccessconstraints_id), 
                INDEX IDX_8DECBE50A76ED395 (user_id), 
                PRIMARY KEY(
                    moocaccessconstraints_id, user_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            ALTER TABLE claro_mooc_owner CHANGE name name VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_users 
            DROP FOREIGN KEY FK_8DECBE50DDA4386D
        ");
        $this->addSql("
            DROP TABLE claro_mooc_access_constraints
        ");
        $this->addSql("
            DROP TABLE claro_mooc_constraints_to_users
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_owner CHANGE name name VARCHAR(255) DEFAULT NULL
        ");
    }
}