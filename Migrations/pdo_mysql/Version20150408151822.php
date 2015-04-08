<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/08 03:04:32
 */
class Version20150408151822 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DELETE FROM claro_analytics_badge_mooc_stats WHERE 1 = 1
        ");
    }

    public function down(Schema $schema)
    {
        
    }
}