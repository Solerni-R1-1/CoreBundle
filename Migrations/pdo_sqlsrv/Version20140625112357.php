<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/25 11:23:59
 */
class Version20140625112357 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_mooc.desc_img', 
            'illustration_path', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD illustration_name NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN illustration_path NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD desc_img NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN illustration_path
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN illustration_name
        ");
    }
}