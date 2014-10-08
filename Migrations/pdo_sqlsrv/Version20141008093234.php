<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/08 09:32:36
 */
class Version20141008093234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            ADD nbSuccess INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            ADD nbFail INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            DROP COLUMN nbSuccess
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            DROP COLUMN nbFail
        ");
    }
}