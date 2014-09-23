<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

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
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_74DEBECDA76ED395'
            ) 
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP CONSTRAINT UNIQ_74DEBECDA76ED395 ELSE 
            DROP INDEX UNIQ_74DEBECDA76ED395 ON claro_mooc_sessions_by_users
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_74DEBECD9D070DE7'
            ) 
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP CONSTRAINT UNIQ_74DEBECD9D070DE7 ELSE 
            DROP INDEX UNIQ_74DEBECD9D070DE7 ON claro_mooc_sessions_by_users
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_74DEBECDA96EF72D'
            ) 
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP CONSTRAINT UNIQ_74DEBECDA96EF72D ELSE 
            DROP INDEX UNIQ_74DEBECDA96EF72D ON claro_mooc_sessions_by_users
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_74DEBECDA3C10BE'
            ) 
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP CONSTRAINT UNIQ_74DEBECDA3C10BE ELSE 
            DROP INDEX UNIQ_74DEBECDA3C10BE ON claro_mooc_sessions_by_users
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
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_74DEBECDA76ED395'
            ) 
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP CONSTRAINT IDX_74DEBECDA76ED395 ELSE 
            DROP INDEX IDX_74DEBECDA76ED395 ON claro_mooc_sessions_by_users
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_74DEBECD9D070DE7'
            ) 
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP CONSTRAINT IDX_74DEBECD9D070DE7 ELSE 
            DROP INDEX IDX_74DEBECD9D070DE7 ON claro_mooc_sessions_by_users
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_74DEBECDA96EF72D'
            ) 
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP CONSTRAINT IDX_74DEBECDA96EF72D ELSE 
            DROP INDEX IDX_74DEBECDA96EF72D ON claro_mooc_sessions_by_users
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_74DEBECDA3C10BE'
            ) 
            ALTER TABLE claro_mooc_sessions_by_users 
            DROP CONSTRAINT IDX_74DEBECDA3C10BE ELSE 
            DROP INDEX IDX_74DEBECDA3C10BE ON claro_mooc_sessions_by_users
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA76ED395 ON claro_mooc_sessions_by_users (user_id) 
            WHERE user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECD9D070DE7 ON claro_mooc_sessions_by_users (moocSession_id) 
            WHERE moocSession_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA96EF72D ON claro_mooc_sessions_by_users (moocOwner_id) 
            WHERE moocOwner_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA3C10BE ON claro_mooc_sessions_by_users (moocAccessConstraints_id) 
            WHERE moocAccessConstraints_id IS NOT NULL
        ");
    }
}