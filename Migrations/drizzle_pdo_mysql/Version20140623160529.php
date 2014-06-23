<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
                user_id INT NOT NULL, 
                moocsession_id INT NOT NULL, 
                PRIMARY KEY(user_id, moocsession_id), 
                INDEX IDX_D4EF03A0A76ED395 (user_id), 
                INDEX IDX_D4EF03A0D25A0E37 (moocsession_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_session (
                id INT AUTO_INCREMENT NOT NULL, 
                mooc_id INT NOT NULL, 
                start_date DATETIME NOT NULL, 
                end_date DATETIME NOT NULL, 
                start_inscription_date DATETIME NOT NULL, 
                end_inscription_date DATETIME NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                max_users INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_B0164DD4255EEB87 (mooc_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_mooc (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                alias VARCHAR(255) NOT NULL, 
                description TEXT NOT NULL, 
                desc_img VARCHAR(255) NOT NULL, 
                post_end_action INT NOT NULL, 
                is_public BOOLEAN NOT NULL, 
                duration VARCHAR(255) NOT NULL, 
                weekly_time VARCHAR(255) NOT NULL, 
                cost INT NOT NULL, 
                `language` VARCHAR(10) NOT NULL, 
                has_video BOOLEAN NOT NULL, 
                has_subtitle BOOLEAN NOT NULL, 
                prerequisites TEXT NOT NULL, 
                team_description TEXT NOT NULL, 
                has_facebook_share BOOLEAN NOT NULL, 
                has_tweeter_share BOOLEAN NOT NULL, 
                has_gplus_share BOOLEAN NOT NULL, 
                has_linkin_share BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_FB43C54E82D40A1F (workspace_id)
            )
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
            ADD mooc_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_D4EF03A0D25A0E37
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D9028545255EEB87
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            DROP FOREIGN KEY FK_B0164DD4255EEB87
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
            DROP INDEX IDX_D9028545255EEB87 ON claro_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP mooc_id
        ");
    }
}