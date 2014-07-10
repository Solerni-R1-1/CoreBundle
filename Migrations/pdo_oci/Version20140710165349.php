<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

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
                mooc_id NUMBER(10) NOT NULL, 
                moocaccessconstraints_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(
                    mooc_id, moocaccessconstraints_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_15CE6C4D255EEB87 ON mooc_moocaccessconstraints (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_15CE6C4DDDA4386D ON mooc_moocaccessconstraints (moocaccessconstraints_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_moocs (
                moocaccessconstraints_id NUMBER(10) NOT NULL, 
                mooc_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(
                    moocaccessconstraints_id, mooc_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_99FD2CF2DDA4386D ON claro_mooc_constraints_to_moocs (moocaccessconstraints_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_99FD2CF2255EEB87 ON claro_mooc_constraints_to_moocs (mooc_id)
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
            DROP INDEX UNIQ_8C229ACDA96EF72D
        ");
        $this->addSql("
            CREATE INDEX IDX_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id)
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
            DROP INDEX IDX_8C229ACDA96EF72D
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id)
        ");
    }
}