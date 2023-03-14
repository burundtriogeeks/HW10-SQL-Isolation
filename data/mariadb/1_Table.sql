USE `test`;

CREATE TABLE `users` (
                         `id` int(10) UNSIGNED NOT NULL,
                         `name` char(50) NOT NULL,
                         `age` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
    ADD KEY `index_age` (`age`);

