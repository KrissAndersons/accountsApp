Linux terminal setup:

git clone https://github.com/KrissAndersons/accountsApp.git

Go to project directory:

docker-compose up

After a while container shoud be up and runing, open new teminal in same directory:

composer install

docker-compose exec -u 1000 fpm bash

Now you are in container, run comand to set up db tables and data (required for aplicationTests):

symfony console doctrine:migrations:migrate

symfony console doctrine:fixtures:load


Now you shoud be able to access accounts app at:

http://localhost:8000/

for running smallTests create test db:

symfony console doctrine:database:create --env="test"

run all tests from container:

symfony php bin/phpunit

