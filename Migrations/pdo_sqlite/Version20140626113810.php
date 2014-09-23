<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/26 11:38:11
 */
class Version20140626113810 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD COLUMN about_page_description CLOB DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_FB43C54E82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_mooc AS 
            SELECT id, 
            workspace_id, 
            title, 
            alias, 
            description, 
            illustration_path, 
            illustration_name, 
            post_end_action, 
            is_public, 
            duration, 
            weekly_time, 
            cost, 
            language, 
            has_video, 
            has_subtitle, 
            prerequisites, 
            team_description, 
            has_facebook_share, 
            has_tweeter_share, 
            has_gplus_share, 
            has_linkin_share 
            FROM claro_mooc
        ");
        $this->addSql("
            DROP TABLE claro_mooc
        ");
        $this->addSql("
            CREATE TABLE claro_mooc (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                alias VARCHAR(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                illustration_path VARCHAR(255) DEFAULT NULL, 
                illustration_name VARCHAR(255) DEFAULT NULL, 
                post_end_action INTEGER DEFAULT NULL, 
                is_public BOOLEAN DEFAULT NULL, 
                duration VARCHAR(255) DEFAULT NULL, 
                weekly_time VARCHAR(255) DEFAULT NULL, 
                cost INTEGER DEFAULT NULL, 
                language VARCHAR(10) DEFAULT NULL, 
                has_video BOOLEAN DEFAULT NULL, 
                has_subtitle BOOLEAN DEFAULT NULL, 
                prerequisites CLOB DEFAULT NULL, 
                team_description CLOB DEFAULT NULL, 
                has_facebook_share BOOLEAN DEFAULT NULL, 
                has_tweeter_share BOOLEAN DEFAULT NULL, 
                has_gplus_share BOOLEAN DEFAULT NULL, 
                has_linkin_share BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_FB43C54E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc (
                id, workspace_id, title, alias, description, 
                illustration_path, illustration_name, 
                post_end_action, is_public, duration, 
                weekly_time, cost, language, has_video, 
                has_subtitle, prerequisites, team_description, 
                has_facebook_share, has_tweeter_share, 
                has_gplus_share, has_linkin_share
            ) 
            SELECT id, 
            workspace_id, 
            title, 
            alias, 
            description, 
            illustration_path, 
            illustration_name, 
            post_end_action, 
            is_public, 
            duration, 
            weekly_time, 
            cost, 
            language, 
            has_video, 
            has_subtitle, 
            prerequisites, 
            team_description, 
            has_facebook_share, 
            has_tweeter_share, 
            has_gplus_share, 
            has_linkin_share 
            FROM __temp__claro_mooc
        ");
        $this->addSql("
            DROP TABLE __temp__claro_mooc
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54E82D40A1F ON claro_mooc (workspace_id)
        ");
    }
}