<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 11:41:03
 */
class Version20141002114101 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_E4E824D6AA9E377A'
            ) 
            ALTER TABLE claro_analytics_mooc_connections 
            DROP CONSTRAINT UNIQ_E4E824D6AA9E377A ELSE 
            DROP INDEX UNIQ_E4E824D6AA9E377A ON claro_analytics_mooc_connections
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_6CA80F74AA9E377A'
            ) 
            ALTER TABLE claro_analytics_mooc_subscriptions 
            DROP CONSTRAINT UNIQ_6CA80F74AA9E377A ELSE 
            DROP INDEX UNIQ_6CA80F74AA9E377A ON claro_analytics_mooc_subscriptions
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4E824D6AA9E377A ON claro_analytics_mooc_connections (date) 
            WHERE date IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6CA80F74AA9E377A ON claro_analytics_mooc_subscriptions (date) 
            WHERE date IS NOT NULL
        ");
    }
}