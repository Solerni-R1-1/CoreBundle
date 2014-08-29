<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/29 11:27:09
 */
class Version20140829112707 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD badgesText VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD badgesUrl VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD googleAnalyticsToken VARCHAR(MAX)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN badgesText
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN badgesUrl
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN googleAnalyticsToken
        ");
    }
}