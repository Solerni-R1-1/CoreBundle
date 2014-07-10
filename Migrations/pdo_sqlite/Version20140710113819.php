<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

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
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                whitelist CLOB DEFAULT NULL, 
                patterns CLOB DEFAULT NULL, 
                moocOwner_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_users (
                moocaccessconstraints_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(
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
            CREATE TEMPORARY TABLE __temp__claro_mooc_owner AS 
            SELECT id, 
            name, 
            description, 
            logo_path, 
            dressing_path 
            FROM claro_mooc_owner
        ");
        $this->addSql("
            DROP TABLE claro_mooc_owner
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_owner (
                id INTEGER NOT NULL, 
                description CLOB DEFAULT NULL, 
                logo_path VARCHAR(255) DEFAULT NULL, 
                dressing_path VARCHAR(255) DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc_owner (
                id, name, description, logo_path, dressing_path
            ) 
            SELECT id, 
            name, 
            description, 
            logo_path, 
            dressing_path 
            FROM __temp__claro_mooc_owner
        ");
        $this->addSql("
            DROP TABLE __temp__claro_mooc_owner
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_mooc_access_constraints
        ");
        $this->addSql("
            DROP TABLE claro_mooc_constraints_to_users
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_mooc_owner AS 
            SELECT id, 
            name, 
            description, 
            logo_path, 
            dressing_path 
            FROM claro_mooc_owner
        ");
        $this->addSql("
            DROP TABLE claro_mooc_owner
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_owner (
                id INTEGER NOT NULL, 
                description CLOB DEFAULT NULL, 
                logo_path VARCHAR(255) DEFAULT NULL, 
                dressing_path VARCHAR(255) DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc_owner (
                id, name, description, logo_path, dressing_path
            ) 
            SELECT id, 
            name, 
            description, 
            logo_path, 
            dressing_path 
            FROM __temp__claro_mooc_owner
        ");
        $this->addSql("
            DROP TABLE __temp__claro_mooc_owner
        ");
    }
}