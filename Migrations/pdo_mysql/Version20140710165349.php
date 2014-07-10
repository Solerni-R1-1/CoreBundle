<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/10 04:53:50
 */
class Version20140710165349 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE mooc_moocaccessconstraints (
                mooc_id INT NOT NULL, 
                moocaccessconstraints_id INT NOT NULL, 
                INDEX IDX_15CE6C4D255EEB87 (mooc_id), 
                INDEX IDX_15CE6C4DDDA4386D (moocaccessconstraints_id), 
                PRIMARY KEY(
                    mooc_id, moocaccessconstraints_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_moocs (
                moocaccessconstraints_id INT NOT NULL, 
                mooc_id INT NOT NULL, 
                INDEX IDX_99FD2CF2DDA4386D (moocaccessconstraints_id), 
                INDEX IDX_99FD2CF2255EEB87 (mooc_id), 
                PRIMARY KEY(
                    moocaccessconstraints_id, mooc_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE mooc_moocaccessconstraints 
            ADD CONSTRAINT FK_15CE6C4D255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE mooc_moocaccessconstraints 
            ADD CONSTRAINT FK_15CE6C4DDDA4386D FOREIGN KEY (moocaccessconstraints_id) 
            REFERENCES claro_mooc_access_constraints (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            ADD CONSTRAINT FK_99FD2CF2DDA4386D FOREIGN KEY (moocaccessconstraints_id) 
            REFERENCES claro_mooc_access_constraints (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            ADD CONSTRAINT FK_99FD2CF2255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            DROP INDEX UNIQ_8C229ACDA96EF72D, 
            ADD INDEX IDX_8C229ACDA96EF72D (moocOwner_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE mooc_moocaccessconstraints
        ");
        $this->addSql("
            DROP TABLE claro_mooc_constraints_to_moocs
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            DROP INDEX IDX_8C229ACDA96EF72D, 
            ADD UNIQUE INDEX UNIQ_8C229ACDA96EF72D (moocOwner_id)
        ");
    }
}