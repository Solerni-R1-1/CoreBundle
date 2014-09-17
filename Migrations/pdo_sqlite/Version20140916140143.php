<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/16 02:01:45
 */
class Version20140916140143 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_group_mooc_session (
                moocsession_id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                PRIMARY KEY(moocsession_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F2D4BA29D25A0E37 ON claro_group_mooc_session (moocsession_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F2D4BA29FE54D947 ON claro_group_mooc_session (group_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_group_mooc_session
        ");
    }
}