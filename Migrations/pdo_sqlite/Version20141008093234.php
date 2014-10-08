<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/08 09:32:36
 */
class Version20141008093234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            ADD COLUMN nbSuccess INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            ADD COLUMN nbFail INTEGER NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_B132BFD382D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_B132BFD3F7A2C2FC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_analytics_badge_mooc_stats AS 
            SELECT id, 
            workspace_id, 
            badge_id, 
            badgeType, 
            date, 
            nbParticipations 
            FROM claro_analytics_badge_mooc_stats
        ");
        $this->addSql("
            DROP TABLE claro_analytics_badge_mooc_stats
        ");
        $this->addSql("
            CREATE TABLE claro_analytics_badge_mooc_stats (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                badge_id INTEGER DEFAULT NULL, 
                badgeType VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                nbParticipations INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_B132BFD382D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_B132BFD3F7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_analytics_badge_mooc_stats (
                id, workspace_id, badge_id, badgeType, 
                date, nbParticipations
            ) 
            SELECT id, 
            workspace_id, 
            badge_id, 
            badgeType, 
            date, 
            nbParticipations 
            FROM __temp__claro_analytics_badge_mooc_stats
        ");
        $this->addSql("
            DROP TABLE __temp__claro_analytics_badge_mooc_stats
        ");
        $this->addSql("
            CREATE INDEX IDX_B132BFD382D40A1F ON claro_analytics_badge_mooc_stats (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B132BFD3F7A2C2FC ON claro_analytics_badge_mooc_stats (badge_id)
        ");
    }
}