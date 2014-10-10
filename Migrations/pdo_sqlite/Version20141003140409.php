<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/03 02:04:11
 */
class Version20141003140409 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD COLUMN nbActiveUsers VARCHAR(255) NOT NULL
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