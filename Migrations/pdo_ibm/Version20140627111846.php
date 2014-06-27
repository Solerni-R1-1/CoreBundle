<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

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
            ALTER TABLE claro_mooc_session ALTER start_date start_date TIMESTAMP(0) DEFAULT NULL ALTER end_date end_date TIMESTAMP(0) DEFAULT NULL ALTER start_inscription_date start_inscription_date TIMESTAMP(0) DEFAULT NULL ALTER end_inscription_date end_inscription_date TIMESTAMP(0) DEFAULT NULL ALTER title title VARCHAR(255) DEFAULT NULL ALTER max_users max_users INTEGER DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN illustration_name
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD COLUMN illustration_name VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER start_date start_date TIMESTAMP(0) NOT NULL ALTER end_date end_date TIMESTAMP(0) NOT NULL ALTER start_inscription_date start_inscription_date TIMESTAMP(0) NOT NULL ALTER end_inscription_date end_inscription_date TIMESTAMP(0) NOT NULL ALTER title title VARCHAR(255) NOT NULL ALTER max_users max_users INTEGER NOT NULL
        ");
    }
}