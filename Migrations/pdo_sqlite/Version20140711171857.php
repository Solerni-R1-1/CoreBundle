<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/11 05:18:58
 */
class Version20140711171857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_moocs (
                mooc_id INTEGER NOT NULL, 
                moocaccessconstraints_id INTEGER NOT NULL, 
                PRIMARY KEY(
                    mooc_id, moocaccessconstraints_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_99FD2CF2255EEB87 ON claro_mooc_constraints_to_moocs (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_99FD2CF2DDA4386D ON claro_mooc_constraints_to_moocs (moocaccessconstraints_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_owner (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                logo_path VARCHAR(255) DEFAULT NULL, 
                dressing_path VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_access_constraints (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                whitelist CLOB DEFAULT NULL, 
                patterns CLOB DEFAULT NULL, 
                moocOwner_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id)
        ");
        $this->addSql("
            DROP INDEX IDX_D4EF03A0A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_D4EF03A0D25A0E37
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_mooc_session AS 
            SELECT user_id, 
            moocsession_id 
            FROM claro_user_mooc_session
        ");
        $this->addSql("
            DROP TABLE claro_user_mooc_session
        ");
        $this->addSql("
            CREATE TABLE claro_user_mooc_session (
                moocsession_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(moocsession_id, user_id), 
                CONSTRAINT FK_D4EF03A0A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D4EF03A0D25A0E37 FOREIGN KEY (moocsession_id) 
                REFERENCES claro_mooc_session (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_mooc_session (user_id, moocsession_id) 
            SELECT user_id, 
            moocsession_id 
            FROM __temp__claro_user_mooc_session
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_mooc_session
        ");
        $this->addSql("
            CREATE INDEX IDX_D4EF03A0A76ED395 ON claro_user_mooc_session (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4EF03A0D25A0E37 ON claro_user_mooc_session (moocsession_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_FB43C54E82D40A1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_FB43C54ECDF80196
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_mooc AS 
            SELECT id, 
            workspace_id, 
            lesson_id, 
            title, 
            alias, 
            description, 
            illustration_path, 
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
            has_linkin_share, 
            about_page_description 
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
                owner_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                alias VARCHAR(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                illustration_path VARCHAR(255) DEFAULT NULL, 
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
                about_page_description CLOB DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_FB43C54E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_FB43C54ECDF80196 FOREIGN KEY (lesson_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_FB43C54E7E3C61F9 FOREIGN KEY (owner_id) 
                REFERENCES claro_mooc_owner (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc (
                id, workspace_id, lesson_id, title, 
                alias, description, illustration_path, 
                post_end_action, is_public, duration, 
                weekly_time, cost, language, has_video, 
                has_subtitle, prerequisites, team_description, 
                has_facebook_share, has_tweeter_share, 
                has_gplus_share, has_linkin_share, 
                about_page_description
            ) 
            SELECT id, 
            workspace_id, 
            lesson_id, 
            title, 
            alias, 
            description, 
            illustration_path, 
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
            has_linkin_share, 
            about_page_description 
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
            CREATE INDEX IDX_FB43C54E7E3C61F9 ON claro_mooc (owner_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_mooc_constraints_to_moocs
        ");
        $this->addSql("
            DROP TABLE claro_mooc_owner
        ");
        $this->addSql("
            DROP TABLE claro_mooc_access_constraints
        ");
        $this->addSql("
            DROP INDEX UNIQ_FB43C54E82D40A1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_FB43C54ECDF80196
        ");
        $this->addSql("
            DROP INDEX IDX_FB43C54E7E3C61F9
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_mooc AS 
            SELECT id, 
            workspace_id, 
            lesson_id, 
            title, 
            alias, 
            description, 
            about_page_description, 
            illustration_path, 
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
                lesson_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                alias VARCHAR(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                about_page_description CLOB DEFAULT NULL, 
                illustration_path VARCHAR(255) DEFAULT NULL, 
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
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_FB43C54ECDF80196 FOREIGN KEY (lesson_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc (
                id, workspace_id, lesson_id, title, 
                alias, description, about_page_description, 
                illustration_path, post_end_action, 
                is_public, duration, weekly_time, 
                cost, language, has_video, has_subtitle, 
                prerequisites, team_description, 
                has_facebook_share, has_tweeter_share, 
                has_gplus_share, has_linkin_share
            ) 
            SELECT id, 
            workspace_id, 
            lesson_id, 
            title, 
            alias, 
            description, 
            about_page_description, 
            illustration_path, 
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
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54ECDF80196 ON claro_mooc (lesson_id)
        ");
        $this->addSql("
            DROP INDEX IDX_D4EF03A0D25A0E37
        ");
        $this->addSql("
            DROP INDEX IDX_D4EF03A0A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_mooc_session AS 
            SELECT moocsession_id, 
            user_id 
            FROM claro_user_mooc_session
        ");
        $this->addSql("
            DROP TABLE claro_user_mooc_session
        ");
        $this->addSql("
            CREATE TABLE claro_user_mooc_session (
                user_id INTEGER NOT NULL, 
                moocsession_id INTEGER NOT NULL, 
                PRIMARY KEY(user_id, moocsession_id), 
                CONSTRAINT FK_D4EF03A0D25A0E37 FOREIGN KEY (moocsession_id) 
                REFERENCES claro_mooc_session (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D4EF03A0A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_mooc_session (moocsession_id, user_id) 
            SELECT moocsession_id, 
            user_id 
            FROM __temp__claro_user_mooc_session
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_mooc_session
        ");
        $this->addSql("
            CREATE INDEX IDX_D4EF03A0D25A0E37 ON claro_user_mooc_session (moocsession_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4EF03A0A76ED395 ON claro_user_mooc_session (user_id)
        ");
    }
}