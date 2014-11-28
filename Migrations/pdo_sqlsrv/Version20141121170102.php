<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/21 05:01:12
 */
class Version20141121170102 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD showWorkGroup BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD workGroup VARCHAR(MAX)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN showWorkGroup
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN workGroup
        ");
    }
}