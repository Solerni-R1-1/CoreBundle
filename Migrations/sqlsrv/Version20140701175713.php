<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/01 05:57:15
 */
class Version20140701175713 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            ADD forum_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            ADD CONSTRAINT FK_B0164DD429CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B0164DD429CCBAD0 ON claro_mooc_session (forum_id) 
            WHERE forum_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD lesson_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD CONSTRAINT FK_FB43C54ECDF80196 FOREIGN KEY (lesson_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54ECDF80196 ON claro_mooc (lesson_id) 
            WHERE lesson_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN lesson_id
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP CONSTRAINT FK_FB43C54ECDF80196
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_FB43C54ECDF80196'
            ) 
            ALTER TABLE claro_mooc 
            DROP CONSTRAINT UNIQ_FB43C54ECDF80196 ELSE 
            DROP INDEX UNIQ_FB43C54ECDF80196 ON claro_mooc
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            DROP COLUMN forum_id
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            DROP CONSTRAINT FK_B0164DD429CCBAD0
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_B0164DD429CCBAD0'
            ) 
            ALTER TABLE claro_mooc_session 
            DROP CONSTRAINT UNIQ_B0164DD429CCBAD0 ELSE 
            DROP INDEX UNIQ_B0164DD429CCBAD0 ON claro_mooc_session
        ");
    }
}