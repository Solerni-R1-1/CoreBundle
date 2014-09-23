<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/11 05:18:58
 */
class Version20140711171857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_moocs (
                mooc_id NUMBER(10) NOT NULL, 
                moocaccessconstraints_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(
                    mooc_id, moocaccessconstraints_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_99FD2CF2255EEB87 ON claro_mooc_constraints_to_moocs (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_99FD2CF2DDA4386D ON claro_mooc_constraints_to_moocs (moocaccessconstraints_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_owner (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                logo_path VARCHAR2(255) DEFAULT NULL, 
                dressing_path VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_MOOC_OWNER' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_MOOC_OWNER ADD CONSTRAINT CLARO_MOOC_OWNER_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_MOOC_OWNER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_MOOC_OWNER_AI_PK BEFORE INSERT ON CLARO_MOOC_OWNER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_MOOC_OWNER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_MOOC_OWNER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_MOOC_OWNER_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_MOOC_OWNER_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_access_constraints (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                whitelist CLOB DEFAULT NULL, 
                patterns CLOB DEFAULT NULL, 
                moocOwner_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_MOOC_ACCESS_CONSTRAINTS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_MOOC_ACCESS_CONSTRAINTS ADD CONSTRAINT CLARO_MOOC_ACCESS_CONSTRAINTS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_MOOC_ACCESS_CONSTRAINTS_AI_PK BEFORE INSERT ON CLARO_MOOC_ACCESS_CONSTRAINTS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            ADD CONSTRAINT FK_99FD2CF2255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            ADD CONSTRAINT FK_99FD2CF2DDA4386D FOREIGN KEY (moocaccessconstraints_id) 
            REFERENCES claro_mooc_access_constraints (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            ADD CONSTRAINT FK_8C229ACDA96EF72D FOREIGN KEY (moocOwner_id) 
            REFERENCES claro_mooc_owner (id)
        ");
        $this->addSql("
            DROP INDEX primary
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD PRIMARY KEY (moocsession_id, user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD (
                owner_id NUMBER(10) DEFAULT NULL
            )
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
            DROP CONSTRAINT FK_FB43C54E7E3C61F9
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            DROP CONSTRAINT FK_8C229ACDA96EF72D
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_moocs 
            DROP CONSTRAINT FK_99FD2CF2DDA4386D
        ");
        $this->addSql("
            DROP TABLE claro_mooc_constraints_to_moocs
        ");
        $this->addSql("
            DROP TABLE claro_mooc_owner
        ");
        $this->addSql("
            DROP TABLE claro_mooc_access_constraints
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP (owner_id)
        ");
        $this->addSql("
            DROP INDEX IDX_FB43C54E7E3C61F9
        ");
        $this->addSql("
            DROP INDEX PRIMARY
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD PRIMARY KEY (user_id, moocsession_id)
        ");
    }
}