<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 11:41:02
 */
class Version20141002114101 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_E4E824D6AA9E377A
        ");
        $this->addSql("
            DROP INDEX UNIQ_6CA80F74AA9E377A
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4E824D6AA9E377A ON claro_analytics_mooc_connections (\"date\")
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6CA80F74AA9E377A ON claro_analytics_mooc_subscriptions (\"date\")
        ");
    }
}