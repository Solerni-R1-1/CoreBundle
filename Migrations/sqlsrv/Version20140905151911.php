<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/05 03:19:13
 */
class Version20140905151911 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD blog_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD CONSTRAINT FK_FB43C54EDAE07E97 FOREIGN KEY (blog_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54EDAE07E97 ON claro_mooc (blog_id) 
            WHERE blog_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN blog_id
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP CONSTRAINT FK_FB43C54EDAE07E97
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_FB43C54EDAE07E97'
            ) 
            ALTER TABLE claro_mooc 
            DROP CONSTRAINT UNIQ_FB43C54EDAE07E97 ELSE 
            DROP INDEX UNIQ_FB43C54EDAE07E97 ON claro_mooc
        ");
    }
}