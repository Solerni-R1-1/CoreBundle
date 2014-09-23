<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/16 11:47:02
 */
class Version20140716114700 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_74DEBECDA76ED395
        ");
        $this->addSql("
            DROP INDEX UNIQ_74DEBECD9D070DE7
        ");
        $this->addSql("
            DROP INDEX UNIQ_74DEBECDA96EF72D
        ");
        $this->addSql("
            DROP INDEX UNIQ_74DEBECDA3C10BE
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_mooc_sessions_by_users AS 
            SELECT id, 
            user_id, 
            moocSession_id, 
            moocOwner_id, 
            moocAccessConstraints_id 
            FROM claro_mooc_sessions_by_users
        ");
        $this->addSql("
            DROP TABLE claro_mooc_sessions_by_users
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_sessions_by_users (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                moocSession_id INTEGER DEFAULT NULL, 
                moocOwner_id INTEGER DEFAULT NULL, 
                moocAccessConstraints_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_74DEBECDA3C10BE FOREIGN KEY (moocAccessConstraints_id) 
                REFERENCES claro_mooc_access_constraints (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_74DEBECD9D070DE7 FOREIGN KEY (moocSession_id) 
                REFERENCES claro_mooc_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_74DEBECDA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_74DEBECDA96EF72D FOREIGN KEY (moocOwner_id) 
                REFERENCES claro_mooc_owner (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc_sessions_by_users (
                id, user_id, moocSession_id, moocOwner_id, 
                moocAccessConstraints_id
            ) 
            SELECT id, 
            user_id, 
            moocSession_id, 
            moocOwner_id, 
            moocAccessConstraints_id 
            FROM __temp__claro_mooc_sessions_by_users
        ");
        $this->addSql("
            DROP TABLE __temp__claro_mooc_sessions_by_users
        ");
        $this->addSql("
            CREATE INDEX IDX_74DEBECDA76ED395 ON claro_mooc_sessions_by_users (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_74DEBECD9D070DE7 ON claro_mooc_sessions_by_users (moocSession_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_74DEBECDA96EF72D ON claro_mooc_sessions_by_users (moocOwner_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_74DEBECDA3C10BE ON claro_mooc_sessions_by_users (moocAccessConstraints_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_74DEBECDA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_74DEBECD9D070DE7
        ");
        $this->addSql("
            DROP INDEX IDX_74DEBECDA96EF72D
        ");
        $this->addSql("
            DROP INDEX IDX_74DEBECDA3C10BE
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_mooc_sessions_by_users AS 
            SELECT id, 
            user_id, 
            moocSession_id, 
            moocOwner_id, 
            moocAccessConstraints_id 
            FROM claro_mooc_sessions_by_users
        ");
        $this->addSql("
            DROP TABLE claro_mooc_sessions_by_users
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_sessions_by_users (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                moocSession_id INTEGER DEFAULT NULL, 
                moocOwner_id INTEGER DEFAULT NULL, 
                moocAccessConstraints_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_74DEBECDA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_74DEBECD9D070DE7 FOREIGN KEY (moocSession_id) 
                REFERENCES claro_mooc_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_74DEBECDA96EF72D FOREIGN KEY (moocOwner_id) 
                REFERENCES claro_mooc_owner (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_74DEBECDA3C10BE FOREIGN KEY (moocAccessConstraints_id) 
                REFERENCES claro_mooc_access_constraints (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_mooc_sessions_by_users (
                id, user_id, moocSession_id, moocOwner_id, 
                moocAccessConstraints_id
            ) 
            SELECT id, 
            user_id, 
            moocSession_id, 
            moocOwner_id, 
            moocAccessConstraints_id 
            FROM __temp__claro_mooc_sessions_by_users
        ");
        $this->addSql("
            DROP TABLE __temp__claro_mooc_sessions_by_users
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA76ED395 ON claro_mooc_sessions_by_users (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECD9D070DE7 ON claro_mooc_sessions_by_users (moocSession_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA96EF72D ON claro_mooc_sessions_by_users (moocOwner_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA3C10BE ON claro_mooc_sessions_by_users (moocAccessConstraints_id)
        ");
    }
}