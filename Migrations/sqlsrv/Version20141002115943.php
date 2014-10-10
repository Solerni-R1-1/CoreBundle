<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 11:59:45
 */
class Version20141002115943 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_mooc_stats (
                id INT IDENTITY NOT NULL, 
                workspace_id INT, 
                nbConnections NVARCHAR(255) NOT NULL, 
                nbSubscriptions NVARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_91BE67E482D40A1F ON claro_analytics_mooc_stats (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD CONSTRAINT FK_91BE67E482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_mooc_stats
        ");
    }
}