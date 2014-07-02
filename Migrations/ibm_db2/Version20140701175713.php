<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

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
            ADD COLUMN forum_id INTEGER DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            ADD CONSTRAINT FK_B0164DD429CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B0164DD429CCBAD0 ON claro_mooc_session (forum_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD COLUMN lesson_id INTEGER DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD CONSTRAINT FK_FB43C54ECDF80196 FOREIGN KEY (lesson_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54ECDF80196 ON claro_mooc (lesson_id)
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
            DROP FOREIGN KEY FK_FB43C54ECDF80196
        ");
        $this->addSql("
            DROP INDEX UNIQ_FB43C54ECDF80196
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            DROP COLUMN forum_id
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            DROP FOREIGN KEY FK_B0164DD429CCBAD0
        ");
        $this->addSql("
            DROP INDEX UNIQ_B0164DD429CCBAD0
        ");
    }
}