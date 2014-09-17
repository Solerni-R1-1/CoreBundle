<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

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
                INDEX IDX_F2D4BA29D25A0E37 (moocsession_id), 
                INDEX IDX_F2D4BA29FE54D947 (group_id), 
                PRIMARY KEY(moocsession_id, group_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_group_mooc_session 
            ADD CONSTRAINT FK_F2D4BA29D25A0E37 FOREIGN KEY (moocsession_id) 
            REFERENCES claro_mooc_session (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_mooc_session 
            ADD CONSTRAINT FK_F2D4BA29FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_group_mooc_session
        ");
    }
}