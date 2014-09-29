<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Manual migration script
 *
 * Creation date: 2014/09/29 11:27:00
 */
class Version20140929112700 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            UPDATE claro_user_public_profile_preferences
			SET allow_message_sending = 1");

		$this->addSql("
            UPDATE claro_user_public_profile_preferences
			SET share_policy = 1
			WHERE share_policy = 0");
    }

    public function down(Schema $schema)
    {

    }
}
