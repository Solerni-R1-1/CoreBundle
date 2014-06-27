<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/27 11:18:48
 */
class Version20140627111846 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER start_date 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER end_date 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER start_inscription_date 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER end_inscription_date 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER title 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER max_users 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP illustration_name
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD illustration_name VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER start_date 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER end_date 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER start_inscription_date 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER end_inscription_date 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER title 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER max_users 
            SET 
                NOT NULL
        ");
    }
}