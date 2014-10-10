<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 04:50:05
 */
class Version20141002165004 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h0 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h1 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h2 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h3 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h4 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h5 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h6 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h7 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h8 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h9 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h10 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h11 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h12 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h13 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h14 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h15 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h16 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h17 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h18 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h19 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h20 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h21 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h22 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN h23 VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_91BE67E482D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_analytics_mooc_stats AS 
            SELECT id, 
            workspace_id, 
            nbConnections, 
            nbSubscriptions, 
            date 
            FROM claro_analytics_mooc_stats
        ");
        $this->addSql("
            DROP TABLE claro_analytics_mooc_stats
        ");
        $this->addSql("
            CREATE TABLE claro_analytics_mooc_stats (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                nbConnections VARCHAR(255) NOT NULL, 
                nbSubscriptions VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_91BE67E482D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_analytics_mooc_stats (
                id, workspace_id, nbConnections, nbSubscriptions, 
                date
            ) 
            SELECT id, 
            workspace_id, 
            nbConnections, 
            nbSubscriptions, 
            date 
            FROM __temp__claro_analytics_mooc_stats
        ");
        $this->addSql("
            DROP TABLE __temp__claro_analytics_mooc_stats
        ");
        $this->addSql("
            CREATE INDEX IDX_91BE67E482D40A1F ON claro_analytics_mooc_stats (workspace_id)
        ");
    }
}