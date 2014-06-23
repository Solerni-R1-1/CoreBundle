<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/23 04:05:31
 */
class Version20140623160529 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_mooc_session (
                user_id INT NOT NULL, 
                moocsession_id INT NOT NULL, 
                PRIMARY KEY (user_id, moocsession_id)
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
                id INT IDENTITY NOT NULL, 
                mooc_id INT NOT NULL, 
                start_date DATETIME2(6) NOT NULL, 
                end_date DATETIME2(6) NOT NULL, 
                start_inscription_date DATETIME2(6) NOT NULL, 
                end_inscription_date DATETIME2(6) NOT NULL, 
                title NVARCHAR(255) NOT NULL, 
                max_users INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B0164DD4255EEB87 ON claro_mooc_session (mooc_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc (
                id INT IDENTITY NOT NULL, 
                workspace_id INT, 
                title NVARCHAR(255) NOT NULL, 
                alias NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX) NOT NULL, 
                desc_img NVARCHAR(255) NOT NULL, 
                post_end_action INT NOT NULL, 
                is_public BIT NOT NULL, 
                duration NVARCHAR(255) NOT NULL, 
                weekly_time NVARCHAR(255) NOT NULL, 
                cost INT NOT NULL, 
                language NVARCHAR(10) NOT NULL, 
                has_video BIT NOT NULL, 
                has_subtitle BIT NOT NULL, 
                prerequisites VARCHAR(MAX) NOT NULL, 
                team_description VARCHAR(MAX) NOT NULL, 
                has_facebook_share BIT NOT NULL, 
                has_tweeter_share BIT NOT NULL, 
                has_gplus_share BIT NOT NULL, 
                has_linkin_share BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54E82D40A1F ON claro_mooc (workspace_id) 
            WHERE workspace_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD CONSTRAINT FK_D4EF03A0A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD CONSTRAINT FK_D4EF03A0D25A0E37 FOREIGN KEY (moocsession_id) 
            REFERENCES claro_mooc_session (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            ADD CONSTRAINT FK_B0164DD4255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD CONSTRAINT FK_FB43C54E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD mooc_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D9028545255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545255EEB87 ON claro_workspace (mooc_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            DROP CONSTRAINT FK_D4EF03A0D25A0E37
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP CONSTRAINT FK_D9028545255EEB87
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            DROP CONSTRAINT FK_B0164DD4255EEB87
        ");
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
            ALTER TABLE claro_workspace 
            DROP COLUMN mooc_id
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_D9028545255EEB87'
            ) 
            ALTER TABLE claro_workspace 
            DROP CONSTRAINT IDX_D9028545255EEB87 ELSE 
            DROP INDEX IDX_D9028545255EEB87 ON claro_workspace
        ");
    }
}