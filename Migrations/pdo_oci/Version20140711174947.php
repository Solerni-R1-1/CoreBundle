<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/11 05:49:48
 */
class Version20140711174947 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_sessions_by_users (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                moocSession_id NUMBER(10) DEFAULT NULL, 
                moocOwner_id NUMBER(10) DEFAULT NULL, 
                moocAccessConstraints_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_MOOC_SESSIONS_BY_USERS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_MOOC_SESSIONS_BY_USERS ADD CONSTRAINT CLARO_MOOC_SESSIONS_BY_USERS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_MOOC_SESSIONS_BY_USERS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_MOOC_SESSIONS_BY_USERS_AI_PK BEFORE INSERT ON CLARO_MOOC_SESSIONS_BY_USERS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_MOOC_SESSIONS_BY_USERS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_MOOC_SESSIONS_BY_USERS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_MOOC_SESSIONS_BY_USERS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_MOOC_SESSIONS_BY_USERS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA76ED395 ON claro_mooc_sessions_by_users (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECD9D070DE7 ON claro_mooc_sessions_by_users (moocSession_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA96EF72D ON claro_mooc_sessions_by_users (moocOwner_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_74DEBECDA3C10BE ON claro_mooc_sessions_by_users (moocAccessConstraints_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            ADD CONSTRAINT FK_74DEBECDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            ADD CONSTRAINT FK_74DEBECD9D070DE7 FOREIGN KEY (moocSession_id) 
            REFERENCES claro_mooc_session (id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            ADD CONSTRAINT FK_74DEBECDA96EF72D FOREIGN KEY (moocOwner_id) 
            REFERENCES claro_mooc_owner (id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_sessions_by_users 
            ADD CONSTRAINT FK_74DEBECDA3C10BE FOREIGN KEY (moocAccessConstraints_id) 
            REFERENCES claro_mooc_access_constraints (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_mooc_sessions_by_users
        ");
    }
}