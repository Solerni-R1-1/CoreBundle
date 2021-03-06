<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/12 11:32:03
 */
class Version20141112113201 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE INDEX action_idx ON claro_log (action)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX action_idx
        ");
    }
}