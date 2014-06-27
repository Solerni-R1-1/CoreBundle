<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

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
            ALTER TABLE claro_mooc_session ALTER COLUMN start_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN end_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN start_inscription_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN end_inscription_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN title NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN max_users INT
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
            ADD illustration_name NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN start_date DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN end_date DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN start_inscription_date DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN end_inscription_date DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN title NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session ALTER COLUMN max_users INT NOT NULL
        ");
    }
}