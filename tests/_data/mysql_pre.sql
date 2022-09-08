/**
 * This is the database schema for testing MySQL support.
 * The database setup in config.php is required to perform then relevant tests:
 */

DROP TABLE IF EXISTS `migration` CASCADE;

DROP TABLE IF EXISTS `oauth2_user_client_scope` CASCADE;
DROP TABLE IF EXISTS `oauth2_user_client` CASCADE;
DROP TABLE IF EXISTS `oauth2_access_token_scope` CASCADE;
DROP TABLE IF EXISTS `oauth2_auth_code_scope` CASCADE;
DROP TABLE IF EXISTS `oauth2_auth_code` CASCADE;
DROP TABLE IF EXISTS `oauth2_client_scope` CASCADE;
DROP TABLE IF EXISTS `oauth2_refresh_token` CASCADE;
DROP TABLE IF EXISTS `oauth2_access_token` CASCADE;
DROP TABLE IF EXISTS `oauth2_client` CASCADE;
DROP TABLE IF EXISTS `oauth2_scope` CASCADE;

DROP TABLE IF EXISTS `user_identity_link` CASCADE; # in case the sample migrations were run against the test database
DROP TABLE IF EXISTS `user` CASCADE;

CREATE TABLE `user`
(
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `email_address` VARCHAR(255) NOT NULL,
    `latest_authenticated_at` INT,
    `enabled` TINYINT(1) DEFAULT 1 NOT NULL,
    `created_at` INT NOT NULL,
    `updated_at` INT NOT NULL
);

ALTER TABLE `user` AUTO_INCREMENT = 1000;

INSERT INTO `user` VALUES
(
    123,
    'test.user',
    '$2y$10$PtIeyOB1.rPPVHjgTzCO5eSNPS1vdOCNp4nk1IvA2FKYu6jslFVNK', # "password"
    'test.user@test.test',
    UNIX_TIMESTAMP() - 3600,
    1,
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP()
),
(
    124,
    'test.user2',
    '$2y$10$PtIeyOB1.rPPVHjgTzCO5eSNPS1vdOCNp4nk1IvA2FKYu6jslFVNK', # "password"
    'test.user2@test.test',
    UNIX_TIMESTAMP() - 3600,
    1,
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP()
);
