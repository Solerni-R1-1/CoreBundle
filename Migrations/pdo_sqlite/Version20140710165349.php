<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

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
                mooc_id INTEGER NOT NULL, 
                moocaccessconstraints_id INTEGER NOT NULL, 
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
                moocaccessconstraints_id INTEGER NOT NULL, 
                mooc_id INTEGER NOT NULL, 
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
            DROP INDEX UNIQ_8C229ACDA96EF72D
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_mooc_access_constraints AS 
            SELECT id, 
            name, 
            whitelist, 
            patterns, 
            moocOwner_id 
            FROM claro_mooc_access_constraints
        ");
        $this->addSql("
            DROP TABLE claro_mooc_access_constraints
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_access_constraints (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                whitelist CLOB DEFAULT NULL, 
                patterns CLOB DEFAULT NULL, 
                moocOwner_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_8C229ACDA96EF72D FOREIGN KEY (moocOwner_id) 
                REFERENCES claro_mooc_owner (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc_access_constraints (
                id, name, whitelist, patterns, moocOwner_id
            ) 
            SELECT id, 
            name, 
            whitelist, 
            patterns, 
            moocOwner_id 
            FROM __temp__claro_mooc_access_constraints
        ");
        $this->addSql("
            DROP TABLE __temp__claro_mooc_access_constraints
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
            CREATE TEMPORARY TABLE __temp__claro_mooc_access_constraints AS 
            SELECT id, 
            name, 
            whitelist, 
            patterns, 
            moocOwner_id 
            FROM claro_mooc_access_constraints
        ");
        $this->addSql("
            DROP TABLE claro_mooc_access_constraints
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_access_constraints (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                whitelist CLOB DEFAULT NULL, 
                patterns CLOB DEFAULT NULL, 
                moocOwner_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_8C229ACDA96EF72D FOREIGN KEY (moocOwner_id) 
                REFERENCES claro_mooc_owner (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc_access_constraints (
                id, name, whitelist, patterns, moocOwner_id
            ) 
            SELECT id, 
            name, 
            whitelist, 
            patterns, 
            moocOwner_id 
            FROM __temp__claro_mooc_access_constraints
        ");
        $this->addSql("
            DROP TABLE __temp__claro_mooc_access_constraints
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id)
        ");
    }
}