<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
            ADD badgesText TEXT DEFAULT NULL, 
            ADD badgesUrl TEXT DEFAULT NULL, 
            ADD googleAnalyticsToken TEXT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP badgesText, 
            DROP badgesUrl, 
            DROP googleAnalyticsToken
        ");
    }
}