<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/07 04:13:10
 */
class Version20141007161307 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_badge_mooc_stats (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                badge_id INTEGER DEFAULT NULL, 
                date DATE NOT NULL, 
                nbParticipations INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B132BFD382D40A1F ON claro_analytics_badge_mooc_stats (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B132BFD3F7A2C2FC ON claro_analytics_badge_mooc_stats (badge_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_badge_mooc_stats
        ");
    }
}