<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/28 05:42:25
 */
class Version20140728174223 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD is_validate BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD key_validate NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN is_validate
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN key_validate
        ");
    }
}