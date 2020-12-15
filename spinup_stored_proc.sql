create
    definer = admin@`%` procedure spinup()
BEGIN
    DECLARE bDone INT;

    DECLARE table_name VARCHAR(50);
    DECLARE col VARCHAR(50);
    DECLARE typ VARCHAR(50);
    DECLARE siz VARCHAR(50);
    DECLARE nn VARCHAR(50);

    DECLARE curs CURSOR FOR SELECT e.entity_name table_name,
                                   d.data_name col,
                                   UCASE(d.data_type) as typ,
                                   CASE WHEN d.data_type = "date" THEN "" ELSE CONCAT("(",COALESCE(d.size,45),")") END  siz,
                                   CASE WHEN d.not_null = 1 THEN "NOT NULL" ELSE "NULL" END as nn
                            FROM enterprise_architecture.data_dictionary as d
                                     LEFT JOIN enterprise_architecture.data_entity e on d.entity_id = e.id
                            WHERE e.physical = 1
                            ORDER BY table_name,col;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET bDone = 1;
    DECLARE CONTINUE HANDLER for 1060 BEGIN END;

    OPEN curs;

    SET bDone = 0;

    SET @str ="DROP SCHEMA IF EXISTS genesis;";
    PREPARE stmt FROM @str;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @str ="CREATE SCHEMA IF NOT EXISTS `genesis`;";
    PREPARE stmt FROM @str;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    REPEAT
        FETCH curs INTO table_name,col,typ,siz,nn;
        SET @str = CONCAT("CREATE TABLE IF NOT EXISTS ","genesis.",table_name,"(id INT NOT NULL AUTO_INCREMENT, CONSTRAINT PK PRIMARY KEY (id));");
        PREPARE stmt FROM @str;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;

        if(col != "id") THEN
            SET @str = CONCAT("ALTER TABLE ","genesis.",table_name," ADD `",col,"` ",typ,"",siz," ",nn,";");
            PREPARE stmt FROM @str;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;

    UNTIL bDone END REPEAT;

    CLOSE curs;
    SET bDone = 0;
    SELECT "DONE";
END;

