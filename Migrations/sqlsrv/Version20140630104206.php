<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/30 10:42:09
 */
class Version20140630104206 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_moocs_to_categories (
                mooc_id INT NOT NULL, 
                mooccategory_id INT NOT NULL, 
                PRIMARY KEY (mooc_id, mooccategory_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F7608CC7255EEB87 ON claro_moocs_to_categories (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F7608CC7BC24924A ON claro_moocs_to_categories (mooccategory_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_category (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_moocs_to_categories 
            ADD CONSTRAINT FK_F7608CC7255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_moocs_to_categories 
            ADD CONSTRAINT FK_F7608CC7BC24924A FOREIGN KEY (mooccategory_id) 
            REFERENCES claro_mooc_category (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_moocs_to_categories 
            DROP CONSTRAINT FK_F7608CC7BC24924A
        ");
        $this->addSql("
            DROP TABLE claro_moocs_to_categories
        ");
        $this->addSql("
            DROP TABLE claro_mooc_category
        ");
    }
}