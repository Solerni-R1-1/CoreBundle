<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/09 05:07:00
 */
class Version20140709170658 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_owner (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                logo_path VARCHAR(255) DEFAULT NULL, 
                dressing_path VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            DROP PRIMARY KEY
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD PRIMARY KEY (moocsession_id, user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD owner_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD CONSTRAINT FK_FB43C54E7E3C61F9 FOREIGN KEY (owner_id) 
            REFERENCES claro_mooc_owner (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FB43C54E7E3C61F9 ON claro_mooc (owner_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP FOREIGN KEY FK_FB43C54E7E3C61F9
        ");
        $this->addSql("
            DROP TABLE claro_mooc_owner
        ");
        $this->addSql("
            DROP INDEX IDX_FB43C54E7E3C61F9 ON claro_mooc
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP owner_id
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            DROP PRIMARY KEY
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD PRIMARY KEY (user_id, moocsession_id)
        ");
    }
}