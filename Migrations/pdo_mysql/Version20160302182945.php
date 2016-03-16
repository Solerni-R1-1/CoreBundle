<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/03/02 06:29:50
 */
class Version20160302182945 extends AbstractMigration
{
    public function up(Schema $schema)
    {   
        $this->addSql("
            ALTER TABLE claro_user 
            ADD notifarticle TINYINT(1) NOT NULL DEFAULT 1,
            ADD notifsujettheme TINYINT(1) NOT NULL DEFAULT 1,
            ADD notifciter TINYINT(1) NOT NULL DEFAULT 1,
            ADD notiflike TINYINT(1) NOT NULL DEFAULT 1,
            CHANGE forumOrder forumOrder INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD notifarticlemooc TINYINT(1) NOT NULL DEFAULT 0,
            ADD notifsujetthememooc TINYINT(1) NOT NULL DEFAULT 0,
            ADD notifcitermooc TINYINT(1) NOT NULL DEFAULT 0,
            ADD notiflikemooc TINYINT(1) NOT NULL DEFAULT 0
        ");
        $this->addSql(" UPDATE claro_user SET notifarticle=0;
        ");
        $this->addSql(" UPDATE claro_user SET notifsujettheme=0;
        ");
        $this->addSql(" UPDATE claro_user SET notifciter=0;
        ");
        $this->addSql(" UPDATE claro_user SET notiflike=0;
        ");
    }


    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP notifarticlemooc, 
            DROP notifsujetthememooc, 
            DROP notifcitermooc, 
            DROP notiflikemooc
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP notifarticle, 
            DROP notifsujettheme, 
            DROP notifciter, 
            DROP notiflike, 
            CHANGE forumOrder forumOrder INT DEFAULT 1 NOT NULL
        ");
    }
}