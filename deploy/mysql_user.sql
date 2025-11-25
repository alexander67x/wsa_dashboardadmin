DROP USER IF EXISTS 'laravel'@'%';
DROP USER IF EXISTS 'laravel'@'127.0.0.1';

CREATE USER 'laravel'@'%' IDENTIFIED WITH mysql_native_password BY 'Larav3l_2025!';
GRANT ALL PRIVILEGES ON pruebawes.* TO 'laravel'@'%';

FLUSH PRIVILEGES;
