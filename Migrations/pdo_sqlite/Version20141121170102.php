<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/21 05:01:12
 */
class Version20141121170102 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD COLUMN showWorkGroup BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD COLUMN workGroup CLOB DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_FB43C54E82D40A1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_FB43C54ECDF80196
        ");
        $this->addSql("
            DROP INDEX UNIQ_FB43C54EDAE07E97
        ");
        $this->addSql("
            DROP INDEX IDX_FB43C54E7E3C61F9
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_mooc AS 
            SELECT id, 
            workspace_id, 
            lesson_id, 
            blog_id, 
            owner_id, 
            title, 
            alias, 
            description, 
            about_page_description, 
            illustration_path, 
            post_end_action, 
            is_public, 
            duration, 
            weekly_time, 
            certification_type, 
            cost, 
            language, 
            has_video, 
            has_subtitle, 
            prerequisites, 
            team_description, 
            has_facebook_share, 
            has_tweeter_share, 
            has_gplus_share, 
            has_linkin_share, 
            badgesText, 
            badgesUrl, 
            knowledgeBadgesUrl, 
            googleAnalyticsToken, 
            showResourceManager 
            FROM claro_mooc
        ");
        $this->addSql("
            DROP TABLE claro_mooc
        ");
        $this->addSql("
            CREATE TABLE claro_mooc (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                lesson_id INTEGER DEFAULT NULL, 
                blog_id INTEGER DEFAULT NULL, 
                owner_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                alias VARCHAR(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                about_page_description CLOB DEFAULT NULL, 
                illustration_path VARCHAR(255) DEFAULT NULL, 
                post_end_action INTEGER DEFAULT NULL, 
                is_public BOOLEAN DEFAULT NULL, 
                duration INTEGER DEFAULT NULL, 
                weekly_time INTEGER DEFAULT NULL, 
                certification_type CLOB NOT NULL, 
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
                badgesText CLOB DEFAULT NULL, 
                badgesUrl CLOB DEFAULT NULL, 
                knowledgeBadgesUrl CLOB DEFAULT NULL, 
                googleAnalyticsToken CLOB DEFAULT NULL, 
                showResourceManager BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_FB43C54E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_FB43C54ECDF80196 FOREIGN KEY (lesson_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_FB43C54EDAE07E97 FOREIGN KEY (blog_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_FB43C54E7E3C61F9 FOREIGN KEY (owner_id) 
                REFERENCES claro_mooc_owner (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc (
                id, workspace_id, lesson_id, blog_id, 
                owner_id, title, alias, description, 
                about_page_description, illustration_path, 
                post_end_action, is_public, duration, 
                weekly_time, certification_type, 
                cost, language, has_video, has_subtitle, 
                prerequisites, team_description, 
                has_facebook_share, has_tweeter_share, 
                has_gplus_share, has_linkin_share, 
                badgesText, badgesUrl, knowledgeBadgesUrl, 
                googleAnalyticsToken, showResourceManager
            ) 
            SELECT id, 
            workspace_id, 
            lesson_id, 
            blog_id, 
            owner_id, 
            title, 
            alias, 
            description, 
            about_page_description, 
            illustration_path, 
            post_end_action, 
            is_public, 
            duration, 
            weekly_time, 
            certification_type, 
            cost, 
            language, 
            has_video, 
            has_subtitle, 
            prerequisites, 
            team_description, 
            has_facebook_share, 
            has_tweeter_share, 
            has_gplus_share, 
            has_linkin_share, 
            badgesText, 
            badgesUrl, 
            knowledgeBadgesUrl, 
            googleAnalyticsToken, 
            showResourceManager 
            FROM __temp__claro_mooc
        ");
        $this->addSql("
            DROP TABLE __temp__claro_mooc
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54E82D40A1F ON claro_mooc (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54ECDF80196 ON claro_mooc (lesson_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54EDAE07E97 ON claro_mooc (blog_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FB43C54E7E3C61F9 ON claro_mooc (owner_id)
        ");
    }
}