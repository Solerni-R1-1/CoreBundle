<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/03 02:42:08
 */
class Version20141003144207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_315980BAA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_315980BA82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_analytics_user_mooc_stats AS 
            SELECT id, 
            workspace_id, 
            user_id, 
            nbPublicationsForum, 
            nbActivity, 
            last_action_date 
            FROM claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            DROP TABLE claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            CREATE TABLE claro_analytics_user_mooc_stats (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                nbPublicationsForum VARCHAR(255) NOT NULL, 
                nbActivity VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_315980BA82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_315980BAA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_analytics_user_mooc_stats (
                id, workspace_id, user_id, nbPublicationsForum, 
                nbActivity, date
            ) 
            SELECT id, 
            workspace_id, 
            user_id, 
            nbPublicationsForum, 
            nbActivity, 
            last_action_date 
            FROM __temp__claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            DROP TABLE __temp__claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            CREATE INDEX IDX_315980BAA76ED395 ON claro_analytics_user_mooc_stats (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_315980BA82D40A1F ON claro_analytics_user_mooc_stats (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_315980BAA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_315980BA82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_analytics_user_mooc_stats AS 
            SELECT id, 
            user_id, 
            workspace_id, 
            nbPublicationsForum, 
            nbActivity, 
            date 
            FROM claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            DROP TABLE claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            CREATE TABLE claro_analytics_user_mooc_stats (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                nbPublicationsForum VARCHAR(255) NOT NULL, 
                nbActivity VARCHAR(255) NOT NULL, 
                last_action_date DATE NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_315980BAA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_315980BA82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_analytics_user_mooc_stats (
                id, user_id, workspace_id, nbPublicationsForum, 
                nbActivity, last_action_date
            ) 
            SELECT id, 
            user_id, 
            workspace_id, 
            nbPublicationsForum, 
            nbActivity, 
            date 
            FROM __temp__claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            DROP TABLE __temp__claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            CREATE INDEX IDX_315980BAA76ED395 ON claro_analytics_user_mooc_stats (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_315980BA82D40A1F ON claro_analytics_user_mooc_stats (workspace_id)
        ");
    }
}