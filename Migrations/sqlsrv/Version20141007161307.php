<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/07 04:13:11
 */
class Version20141007161307 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_badge_mooc_stats (
                id INT IDENTITY NOT NULL, 
                workspace_id INT, 
                badge_id INT, 
                date DATE NOT NULL, 
                nbParticipations INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B132BFD382D40A1F ON claro_analytics_badge_mooc_stats (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B132BFD3F7A2C2FC ON claro_analytics_badge_mooc_stats (badge_id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            ADD CONSTRAINT FK_B132BFD382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            ADD CONSTRAINT FK_B132BFD3F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_badge_mooc_stats
        ");
    }
}