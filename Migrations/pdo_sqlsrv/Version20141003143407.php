<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/03 02:34:08
 */
class Version20141003143407 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_user_mooc_stats (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                workspace_id INT, 
                nbPublicationsForum NVARCHAR(255) NOT NULL, 
                nbActivity NVARCHAR(255) NOT NULL, 
                last_action_date DATE NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_315980BAA76ED395 ON claro_analytics_user_mooc_stats (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_315980BA82D40A1F ON claro_analytics_user_mooc_stats (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_user_mooc_stats 
            ADD CONSTRAINT FK_315980BAA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_user_mooc_stats 
            ADD CONSTRAINT FK_315980BA82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN nbActiveUsers
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD nbActiveUsers NVARCHAR(255) NOT NULL
        ");
    }
}