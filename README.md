# PHP LOGIN MANAGEMENT 
# requirement
- php >= 8.0
- mysql >= 5.7.0
- firebase/php-jwt >= ^6.2

# Config
- restore database dari db folder ke mysql
- database (folder config-database.php):
  - host: localhost
    user: root
    password: 'sesuaikan'
    database: login_management 
  - host: localhost
    user: root
    password: 'sesuaikan'
    database: login_management_test (database untuk testing)

# intallation
- clone project to local directory
- composer install

# Run
- Masuk ke directory public : 'cd public'
- Jalankan script : 'php -S localhost:8080'
- atau buat localserver sendiri