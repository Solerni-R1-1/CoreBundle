<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 09:38:56
 */
class Version20141002093853 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_mooc_connections (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                nbConnections VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4E824D6AA9E377A ON claro_analytics_mooc_connections (date)
        ");
        $this->addSql("
            CREATE INDEX IDX_E4E824D682D40A1F ON claro_analytics_mooc_connections (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_analytics_mooc_subscriptions (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                nbSubscriptions VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6CA80F74AA9E377A ON claro_analytics_mooc_subscriptions (date)
        ");
        $this->addSql("
            CREATE INDEX IDX_6CA80F7482D40A1F ON claro_analytics_mooc_subscriptions (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_mooc_connections
        ");
        $this->addSql("
            DROP TABLE claro_analytics_mooc_subscriptions
        ");
    }
}