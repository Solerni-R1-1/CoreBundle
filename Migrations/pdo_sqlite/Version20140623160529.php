<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/23 04:05:30
 */
class Version20140623160529 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_mooc_session (
                user_id INTEGER NOT NULL, 
                moocsession_id INTEGER NOT NULL, 
                PRIMARY KEY(user_id, moocsession_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D4EF03A0A76ED395 ON claro_user_mooc_session (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4EF03A0D25A0E37 ON claro_user_mooc_session (moocsession_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_session (
                id INTEGER NOT NULL, 
                mooc_id INTEGER NOT NULL, 
                start_date DATETIME NOT NULL, 
                end_date DATETIME NOT NULL, 
                start_inscription_date DATETIME NOT NULL, 
                end_inscription_date DATETIME NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                max_users INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B0164DD4255EEB87 ON claro_mooc_session (mooc_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                alias VARCHAR(255) NOT NULL, 
                description CLOB NOT NULL, 
                desc_img VARCHAR(255) NOT NULL, 
                post_end_action INTEGER NOT NULL, 
                is_public BOOLEAN NOT NULL, 
                duration VARCHAR(255) NOT NULL, 
                weekly_time VARCHAR(255) NOT NULL, 
                cost INTEGER NOT NULL, 
                language VARCHAR(10) NOT NULL, 
                has_video BOOLEAN NOT NULL, 
                has_subtitle BOOLEAN NOT NULL, 
                prerequisites CLOB NOT NULL, 
                team_description CLOB NOT NULL, 
                has_facebook_share BOOLEAN NOT NULL, 
                has_tweeter_share BOOLEAN NOT NULL, 
                has_gplus_share BOOLEAN NOT NULL, 
                has_linkin_share BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54E82D40A1F ON claro_mooc (workspace_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_D902854577153098
        ");
        $this->addSql("
            DROP INDEX UNIQ_D90285452B6FCFB2
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_workspace AS 
            SELECT id, 
            parent_id, 
            user_id, 
            name, 
            code, 
            displayable, 
            guid, 
            self_registration, 
            self_unregistration, 
            discr, 
            lft, 
            lvl, 
            rgt, 
            root, 
            creation_date, 
            description 
            FROM claro_workspace
        ");
        $this->addSql("
            DROP TABLE claro_workspace
        ");
        $this->addSql("
            CREATE TABLE claro_workspace (
                id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                mooc_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                displayable BOOLEAN NOT NULL, 
                guid VARCHAR(255) NOT NULL, 
                self_registration BOOLEAN NOT NULL, 
                self_unregistration BOOLEAN NOT NULL, 
                discr VARCHAR(255) NOT NULL, 
                lft INTEGER DEFAULT NULL, 
                lvl INTEGER DEFAULT NULL, 
                rgt INTEGER DEFAULT NULL, 
                root INTEGER DEFAULT NULL, 
                creation_date INTEGER DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D9028545A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D9028545255EEB87 FOREIGN KEY (mooc_id) 
                REFERENCES claro_mooc (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_workspace (
                id, parent_id, user_id, name, code, 
                displayable, guid, self_registration, 
                self_unregistration, discr, lft, 
                lvl, rgt, root, creation_date, description
            ) 
            SELECT id, 
            parent_id, 
            user_id, 
            name, 
            code, 
            displayable, 
            guid, 
            self_registration, 
            self_unregistration, 
            discr, 
            lft, 
            lvl, 
            rgt, 
            root, 
            creation_date, 
            description 
            FROM __temp__claro_workspace
        ");
        $this->addSql("
            DROP TABLE __temp__claro_workspace
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D902854577153098 ON claro_workspace (code)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D90285452B6FCFB2 ON claro_workspace (guid)
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545A76ED395 ON claro_workspace (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545255EEB87 ON claro_workspace (mooc_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_user_mooc_session
        ");
        $this->addSql("
            DROP TABLE claro_mooc_session
        ");
        $this->addSql("
            DROP TABLE claro_mooc
        ");
        $this->addSql("
            DROP INDEX UNIQ_D902854577153098
        ");
        $this->addSql("
            DROP INDEX UNIQ_D90285452B6FCFB2
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545255EEB87
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_workspace AS 
            SELECT id, 
            user_id, 
            parent_id, 
            name, 
            description, 
            code, 
            displayable, 
            guid, 
            self_registration, 
            self_unregistration, 
            creation_date, 
            discr, 
            lft, 
            lvl, 
            rgt, 
            root 
            FROM claro_workspace
        ");
        $this->addSql("
            DROP TABLE claro_workspace
        ");
        $this->addSql("
            CREATE TABLE claro_workspace (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                code VARCHAR(255) NOT NULL, 
                displayable BOOLEAN NOT NULL, 
                guid VARCHAR(255) NOT NULL, 
                self_registration BOOLEAN NOT NULL, 
                self_unregistration BOOLEAN NOT NULL, 
                creation_date INTEGER DEFAULT NULL, 
                discr VARCHAR(255) NOT NULL, 
                lft INTEGER DEFAULT NULL, 
                lvl INTEGER DEFAULT NULL, 
                rgt INTEGER DEFAULT NULL, 
                root INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D9028545A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_workspace (
                id, user_id, parent_id, name, description, 
                code, displayable, guid, self_registration, 
                self_unregistration, creation_date, 
                discr, lft, lvl, rgt, root
            ) 
            SELECT id, 
            user_id, 
            parent_id, 
            name, 
            description, 
            code, 
            displayable, 
            guid, 
            self_registration, 
            self_unregistration, 
            creation_date, 
            discr, 
            lft, 
            lvl, 
            rgt, 
            root 
            FROM __temp__claro_workspace
        ");
        $this->addSql("
            DROP TABLE __temp__claro_workspace
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D902854577153098 ON claro_workspace (code)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D90285452B6FCFB2 ON claro_workspace (guid)
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545A76ED395 ON claro_workspace (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
    }
}