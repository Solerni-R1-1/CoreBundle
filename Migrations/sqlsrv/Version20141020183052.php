<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/20 06:30:53
 */
class Version20141020183052 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_message 
            DROP COLUMN parent_id
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            DROP COLUMN lft
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            DROP COLUMN lvl
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            DROP COLUMN rgt
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            DROP CONSTRAINT FK_D6FE8DD8727ACA70
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_D6FE8DD8727ACA70'
            ) 
            ALTER TABLE claro_message 
            DROP CONSTRAINT IDX_D6FE8DD8727ACA70 ELSE 
            DROP INDEX IDX_D6FE8DD8727ACA70 ON claro_message
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            ADD CONSTRAINT FK_D6FE8DD816F4F95B FOREIGN KEY (root) 
            REFERENCES claro_message (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD816F4F95B ON claro_message (root)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_message 
            ADD parent_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            ADD lft INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            ADD lvl INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            ADD rgt INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            DROP CONSTRAINT FK_D6FE8DD816F4F95B
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_D6FE8DD816F4F95B'
            ) 
            ALTER TABLE claro_message 
            DROP CONSTRAINT IDX_D6FE8DD816F4F95B ELSE 
            DROP INDEX IDX_D6FE8DD816F4F95B ON claro_message
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            ADD CONSTRAINT FK_D6FE8DD8727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_message (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD8727ACA70 ON claro_message (parent_id)
        ");
    }
}