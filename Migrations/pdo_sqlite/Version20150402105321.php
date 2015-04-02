<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/02 10:53:32
 */
class Version20150402105321 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_CEF67987255EEB87
        ");
        $this->addSql("
            DROP INDEX IDX_CEF67987A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_mooc_preferences AS 
            SELECT mooc_id, 
            user_id, 
            visibility 
            FROM claro_user_mooc_preferences
        ");
        $this->addSql("
            DROP TABLE claro_user_mooc_preferences
        ");
        $this->addSql("
            CREATE TABLE claro_user_mooc_preferences (
                mooc_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                visibility BOOLEAN NOT NULL, 
                PRIMARY KEY(mooc_id, user_id), 
                CONSTRAINT FK_CEF67987255EEB87 FOREIGN KEY (mooc_id) 
                REFERENCES claro_mooc (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_CEF67987A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_mooc_preferences (mooc_id, user_id, visibility) 
            SELECT mooc_id, 
            user_id, 
            visibility 
            FROM __temp__claro_user_mooc_preferences
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_mooc_preferences
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987255EEB87 ON claro_user_mooc_preferences (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987A76ED395 ON claro_user_mooc_preferences (user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_CEF67987255EEB87
        ");
        $this->addSql("
            DROP INDEX IDX_CEF67987A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_mooc_preferences AS 
            SELECT mooc_id, 
            user_id, 
            visibility 
            FROM claro_user_mooc_preferences
        ");
        $this->addSql("
            DROP TABLE claro_user_mooc_preferences
        ");
        $this->addSql("
            CREATE TABLE claro_user_mooc_preferences (
                mooc_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                visibility INTEGER NOT NULL, 
                PRIMARY KEY(mooc_id, user_id), 
                CONSTRAINT FK_CEF67987255EEB87 FOREIGN KEY (mooc_id) 
                REFERENCES claro_mooc (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_CEF67987A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_mooc_preferences (mooc_id, user_id, visibility) 
            SELECT mooc_id, 
            user_id, 
            visibility 
            FROM __temp__claro_user_mooc_preferences
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_mooc_preferences
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987255EEB87 ON claro_user_mooc_preferences (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987A76ED395 ON claro_user_mooc_preferences (user_id)
        ");
    }
}