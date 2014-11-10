<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/05 11:35:40
 */
class Version20141105113534 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE abstractworkspace_user (
                abstractworkspace_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_376C8E26BE2F146C (abstractworkspace_id), 
                INDEX IDX_376C8E26A76ED395 (user_id), 
                PRIMARY KEY(abstractworkspace_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE abstractworkspace_user 
            ADD CONSTRAINT FK_376C8E26BE2F146C FOREIGN KEY (abstractworkspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE abstractworkspace_user 
            ADD CONSTRAINT FK_376C8E26A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE abstractworkspace_user
        ");
    }
}