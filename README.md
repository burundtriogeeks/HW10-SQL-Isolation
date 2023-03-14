# HW10-SQL-Isolation

![Result](https://i.ibb.co/Jnd6Y8R/Screenshot-2023-03-14-at-18-32-48.png)

Use __run.sh__ to start taste

Result of test:

READ-UNCOMMITTED MySQL Dirty read: Yes

READ-UNCOMMITTED Postgres Dirty read: No

READ-UNCOMMITTED MySQL Non-repeatable reads: Yes

READ-UNCOMMITTED Postgres Non-repeatable read: Yes

READ-UNCOMMITTED MySQL Phantom reads: Yes

READ-UNCOMMITTED Postgres Phantom reads: Yes

READ-UNCOMMITTED MySQL Lost update: Yes

READ-UNCOMMITTED Postgres Lost update: Yes

READ-COMMITTED MySQL Dirty read: No

READ-COMMITTED Postgres Dirty read: No

READ-COMMITTED MySQL Non-repeatable reads: Yes

READ-COMMITTED Postgres Non-repeatable read: Yes

READ-COMMITTED MySQL Phantom reads: Yes

READ-COMMITTED Postgres Phantom reads: Yes

READ-COMMITTED MySQL Lost update: Yes

READ-COMMITTED Postgres Lost update: Yes

REPEATABLE-READ MySQL Dirty read: No

REPEATABLE-READ Postgres Dirty read: No

REPEATABLE-READ MySQL Non-repeatable reads: No

REPEATABLE-READ Postgres Non-repeatable read: No

REPEATABLE-READ MySQL Phantom reads: No

REPEATABLE-READ Postgres Phantom reads: No

REPEATABLE-READ MySQL Lost update: Yes

REPEATABLE-READ Postgres Lost update: No

SERIALIZABLE MySQL Dirty read: No

SERIALIZABLE Postgres Dirty read: No

SERIALIZABLE MySQL Non-repeatable reads: No

SERIALIZABLE Postgres Non-repeatable read: No

SERIALIZABLE MySQL Phantom reads: No

SERIALIZABLE Postgres Phantom reads: No

SERIALIZABLE MySQL Lost update: Yes

SERIALIZABLE Postgres Lost update: No
