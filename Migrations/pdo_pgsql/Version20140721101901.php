<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/21 10:19:02
 */
class Version20140721101901 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc ALTER duration TYPE INT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER weekly_time TYPE INT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc ALTER duration TYPE VARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER weekly_time TYPE VARCHAR(255)
        ");
    }
}