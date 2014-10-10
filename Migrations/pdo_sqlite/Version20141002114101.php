<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 11:41:02
 */
class Version20141002114101 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_E4E824D6AA9E377A
        ");
        $this->addSql("
            DROP INDEX IDX_E4E824D682D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_analytics_mooc_connections AS 
            SELECT id, 
            workspace_id, 
            nbConnections, 
            date 
            FROM claro_analytics_mooc_connections
        ");
        $this->addSql("
            DROP TABLE claro_analytics_mooc_connections
        ");
        $this->addSql("
            CREATE TABLE claro_analytics_mooc_connections (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                nbConnections VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4E824D682D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_analytics_mooc_connections (
                id, workspace_id, nbConnections, date
            ) 
            SELECT id, 
            workspace_id, 
            nbConnections, 
            date 
            FROM __temp__claro_analytics_mooc_connections
        ");
        $this->addSql("
            DROP TABLE __temp__claro_analytics_mooc_connections
        ");
        $this->addSql("
            CREATE INDEX IDX_E4E824D682D40A1F ON claro_analytics_mooc_connections (workspace_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_6CA80F74AA9E377A
        ");
        $this->addSql("
            DROP INDEX IDX_6CA80F7482D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_analytics_mooc_subscriptions AS 
            SELECT id, 
            workspace_id, 
            nbSubscriptions, 
            date 
            FROM claro_analytics_mooc_subscriptions
        ");
        $this->addSql("
            DROP TABLE claro_analytics_mooc_subscriptions
        ");
        $this->addSql("
            CREATE TABLE claro_analytics_mooc_subscriptions (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                nbSubscriptions VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6CA80F7482D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_analytics_mooc_subscriptions (
                id, workspace_id, nbSubscriptions, 
                date
            ) 
            SELECT id, 
            workspace_id, 
            nbSubscriptions, 
            date 
            FROM __temp__claro_analytics_mooc_subscriptions
        ");
        $this->addSql("
            DROP TABLE __temp__claro_analytics_mooc_subscriptions
        ");
        $this->addSql("
            CREATE INDEX IDX_6CA80F7482D40A1F ON claro_analytics_mooc_subscriptions (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_E4E824D682D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_analytics_mooc_connections AS 
            SELECT id, 
            workspace_id, 
            nbConnections, 
            date 
            FROM claro_analytics_mooc_connections
        ");
        $this->addSql("
            DROP TABLE claro_analytics_mooc_connections
        ");
        $this->addSql("
            CREATE TABLE claro_analytics_mooc_connections (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                nbConnections VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4E824D682D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_analytics_mooc_connections (
                id, workspace_id, nbConnections, date
            ) 
            SELECT id, 
            workspace_id, 
            nbConnections, 
            date 
            FROM __temp__claro_analytics_mooc_connections
        ");
        $this->addSql("
            DROP TABLE __temp__claro_analytics_mooc_connections
        ");
        $this->addSql("
            CREATE INDEX IDX_E4E824D682D40A1F ON claro_analytics_mooc_connections (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4E824D6AA9E377A ON claro_analytics_mooc_connections (date)
        ");
        $this->addSql("
            DROP INDEX IDX_6CA80F7482D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_analytics_mooc_subscriptions AS 
            SELECT id, 
            workspace_id, 
            nbSubscriptions, 
            date 
            FROM claro_analytics_mooc_subscriptions
        ");
        $this->addSql("
            DROP TABLE claro_analytics_mooc_subscriptions
        ");
        $this->addSql("
            CREATE TABLE claro_analytics_mooc_subscriptions (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                nbSubscriptions VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6CA80F7482D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_analytics_mooc_subscriptions (
                id, workspace_id, nbSubscriptions, 
                date
            ) 
            SELECT id, 
            workspace_id, 
            nbSubscriptions, 
            date 
            FROM __temp__claro_analytics_mooc_subscriptions
        ");
        $this->addSql("
            DROP TABLE __temp__claro_analytics_mooc_subscriptions
        ");
        $this->addSql("
            CREATE INDEX IDX_6CA80F7482D40A1F ON claro_analytics_mooc_subscriptions (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6CA80F74AA9E377A ON claro_analytics_mooc_subscriptions (date)
        ");
    }
}