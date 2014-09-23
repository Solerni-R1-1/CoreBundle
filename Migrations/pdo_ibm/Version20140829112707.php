<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/29 11:27:08
 */
class Version20140829112707 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD COLUMN badgesText CLOB(1M) DEFAULT NULL 
            ADD COLUMN badgesUrl CLOB(1M) DEFAULT NULL 
            ADD COLUMN googleAnalyticsToken CLOB(1M) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN badgesText 
            DROP COLUMN badgesUrl 
            DROP COLUMN googleAnalyticsToken
        ");
    }
}