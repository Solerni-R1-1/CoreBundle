<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

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
                moocsession_id INT NOT NULL, 
                group_id INT NOT NULL, 
                PRIMARY KEY(moocsession_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F2D4BA29D25A0E37 ON claro_group_mooc_session (moocsession_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F2D4BA29FE54D947 ON claro_group_mooc_session (group_id)
        ");
        $this->addSql("
            ALTER TABLE claro_group_mooc_session 
            ADD CONSTRAINT FK_F2D4BA29D25A0E37 FOREIGN KEY (moocsession_id) 
            REFERENCES claro_mooc_session (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_group_mooc_session 
            ADD CONSTRAINT FK_F2D4BA29FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_group_mooc_session
        ");
    }
}