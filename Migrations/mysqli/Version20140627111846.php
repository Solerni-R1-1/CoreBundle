<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/27 11:18:49
 */
class Version20140627111846 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_session CHANGE start_date start_date DATETIME DEFAULT NULL, 
            CHANGE end_date end_date DATETIME DEFAULT NULL, 
            CHANGE start_inscription_date start_inscription_date DATETIME DEFAULT NULL, 
            CHANGE end_inscription_date end_inscription_date DATETIME DEFAULT NULL, 
            CHANGE title title VARCHAR(255) DEFAULT NULL, 
            CHANGE max_users max_users INT DEFAULT NULL
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
            ALTER TABLE claro_mooc_session CHANGE start_date start_date DATETIME NOT NULL, 
            CHANGE end_date end_date DATETIME NOT NULL, 
            CHANGE start_inscription_date start_inscription_date DATETIME NOT NULL, 
            CHANGE end_inscription_date end_inscription_date DATETIME NOT NULL, 
            CHANGE title title VARCHAR(255) NOT NULL, 
            CHANGE max_users max_users INT NOT NULL
        ");
    }
}