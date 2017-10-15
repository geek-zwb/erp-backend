# laravel erp-backend

## 安装
```
# 安装依赖包
composer install

# 生成 APP_KEY
php artisan key:generate

# 修改配置文件，配置数据库等
vim .env

# 生成数据表
php artisan migrate

# 生成一个账号密码，默认为 账号:admin.admin.com 密码:admin 
# 如需修改，请在database/seeds/UsersTableSeeder.php 文件中设置
php artisan db:seed --class=UsersTableSeeder

# 创建生成安全访问令牌时所需的加密密钥，同时，这条命令也会创建用于生成访问令牌的「个人访问」客户端和「密码授权」客户端
php artisan passport:install --force
```