<?php

    $tx_isolations = array("READ-UNCOMMITTED","READ-COMMITTED","REPEATABLE-READ","SERIALIZABLE");

    $mysql_db1 = false;
    $mysql_db2 = false;
    $postgres_db1 = false;
    $postgres_db2 = false;

    function checkResult($type,$status) {
        echo $type.": ".($status? "Yes" : "No")."\n";
    }

    function connectToDb() {
        global $mysql_db1,$mysql_db2, $postgres_db1, $postgres_db2;
        $mysql_db1 = new PDO("mysql:host=host.docker.internal;port=8086;dbname=test;", "root", "root_password");
        $mysql_db2 = new PDO("mysql:host=host.docker.internal;port=8086;dbname=test;", "root", "root_password");
        $postgres_db1 = new PDO("pgsql:host=host.docker.internal;port=8087;dbname=test;", "dev_user", "dev_password");
        $postgres_db2 = new PDO("pgsql:host=host.docker.internal;port=8087;dbname=test;", "dev_user", "dev_password");
    }

    function reconectToDb() {
        global $mysql_db1,$mysql_db2, $postgres_db1, $postgres_db2;
        if ($mysql_db1) { unset($mysql_db1); }
        if ($mysql_db2) { unset($mysql_db2); }
        if ($postgres_db1) { unset($postgres_db1); }
        if ($postgres_db2) { unset($postgres_db2); }

        connectToDb();
    }

    function MySQLDirtyRead($tx_isolation) {
        global $mysql_db1,$mysql_db2;
        reconectToDb();

        $mysql_db1->beginTransaction();
        $mysql_db1->query("SELECT age FROM users WHERE id = 1");

        $mysql_db2->beginTransaction();
        $mysql_db2->query("UPDATE users SET age = 21 WHERE id = 1");

        $res = $mysql_db1->query("SELECT age FROM users WHERE id = 1");
        checkResult("$tx_isolation MySQL Dirty read", $res->fetchAll()[0]["age"] == 21);

        $mysql_db1->commit();
        $mysql_db2->rollBack();
    }

    function MySQLNonRepeatableRead($tx_isolation) {
        global $mysql_db1,$mysql_db2;
        reconectToDb();

        $mysql_db1->beginTransaction();
        $mysql_db1->query("SELECT age FROM users WHERE id = 1");

        $mysql_db2->beginTransaction();
        $mysql_db2->query("UPDATE users SET age = 21 WHERE id = 1");
        $mysql_db2->commit();

        $res = $mysql_db1->query("SELECT age FROM users WHERE id = 1");
        checkResult("$tx_isolation MySQL Non-repeatable reads", $res->fetchAll()[0]["age"] == 21);

        $mysql_db1->commit();
    }

    function MySQLPhantomReads($tx_isolation) {
        global $mysql_db1,$mysql_db2;
        reconectToDb();

        $mysql_db1->beginTransaction();
        $mysql_db1->query("SELECT count(*) as users_count FROM users WHERE age > 17");

        $mysql_db2->beginTransaction();
        $mysql_db2->query("INSERT INTO `users` (`id`,`name`,`age`) VALUES (3,'Carol',26)");
        $mysql_db2->commit();

        $res = $mysql_db1->query("SELECT count(*) as users_count FROM users WHERE age > 17");
        checkResult("$tx_isolation MySQL Phantom reads", $res->fetchAll()[0]["users_count"] == 3);

        $mysql_db1->commit();
    }

    function PostgresDirtyRead($tx_isolation) {
        global $postgres_db1, $postgres_db2;
        reconectToDb();

        $postgres_db1->beginTransaction();
        $postgres_db1->query("SET TRANSACTION ISOLATION LEVEL ".str_replace("-"," ",$tx_isolation));
        $postgres_db1->query("SELECT age FROM users WHERE id = 1");

        $postgres_db2->beginTransaction();
        $postgres_db2->query("SET TRANSACTION ISOLATION LEVEL ".str_replace("-"," ",$tx_isolation));
        $postgres_db2->query("UPDATE users SET age = 21 WHERE id = 1");

        $res = $postgres_db1->query("SELECT age FROM users WHERE id = 1");

        checkResult("$tx_isolation Postgres Dirty read", $res->fetchAll()[0]["age"] == 21);

        $postgres_db1->commit();
        $postgres_db2->rollBack();
    }

    function PostgresNonRepeatableRead($tx_isolation) {
        global $postgres_db1, $postgres_db2;
        reconectToDb();

        $postgres_db1->beginTransaction();
        $postgres_db1->query("SET TRANSACTION ISOLATION LEVEL ".str_replace("-"," ",$tx_isolation));
        $postgres_db1->query("SELECT age FROM users WHERE id = 1");

        $postgres_db2->beginTransaction();
        $postgres_db2->query("SET TRANSACTION ISOLATION LEVEL ".str_replace("-"," ",$tx_isolation));
        $postgres_db2->query("UPDATE users SET age = 21 WHERE id = 1");
        $postgres_db2->commit();

        $res = $postgres_db1->query("SELECT age FROM users WHERE id = 1");
        checkResult("$tx_isolation Postgres Non-repeatable read", $res->fetchAll()[0]["age"] == 21);

        $postgres_db1->commit();
    }

    function PostgresPhantomReads($tx_isolation) {
        global $postgres_db1, $postgres_db2;
        reconectToDb();

        $postgres_db1->beginTransaction();
        $postgres_db1->query("SET TRANSACTION ISOLATION LEVEL ".str_replace("-"," ",$tx_isolation));
        $postgres_db1->query("SELECT count(*) as users_count FROM users WHERE age > 17");

        $postgres_db2->beginTransaction();
        $postgres_db2->query("SET TRANSACTION ISOLATION LEVEL ".str_replace("-"," ",$tx_isolation));
        $postgres_db2->query("INSERT INTO users (id,name,age) VALUES (3,'Carol',26)");
        $postgres_db2->commit();

        $res = $postgres_db1->query("SELECT count(*) as users_count FROM users WHERE age > 17");
        checkResult("$tx_isolation Postgres Phantom reads", $res->fetchAll()[0]["users_count"] == 3);

        $postgres_db1->commit();
    }

    function prepareTest($tx_isolation) {
        global $mysql_db1,$mysql_db2, $postgres_db1, $postgres_db2;
        reconectToDb();

        $mysql_db1->beginTransaction();
        $mysql_db1->query("SET GLOBAL `tx_isolation` = '$tx_isolation'");
        $mysql_db1->query("TRUNCATE `users`");
        $mysql_db1->query("INSERT INTO `users` (`id`,`name`,`age`) VALUES (1,'Alice',20), (2, 'Bob', 25)");
        $mysql_db1->commit();

        $postgres_db1->query("TRUNCATE users");
        $postgres_db1->query("INSERT INTO users (id,name,age) VALUES (1,'Alice',20), (2, 'Bob', 25)");


    }

    foreach ($tx_isolations as $tx_isolation) {


        prepareTest($tx_isolation);


        // Dirty read

        MySQLDirtyRead($tx_isolation);
        PostgresDirtyRead($tx_isolation);


        //Non-repeatable reads

        MySQLNonRepeatableRead($tx_isolation);
        PostgresNonRepeatableRead($tx_isolation);


        //Phantom reads

        MySQLPhantomReads($tx_isolation);
        PostgresPhantomReads($tx_isolation);

    }