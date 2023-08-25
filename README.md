Linux terminal setup:


git clone https://github.com/KrissAndersons/accountsApp.git


docker_compose up

container shoud be is up runing
open new teminal in same directory

composer install

docker-compose exec -u 1000 fpm bash


now you are in container 
run comand to set up db tables and data

symfony console doctrine:migrations:migrate

symfony console doctrine:fixtures:load


now you shoud be able to acces accaunts app at:

http://localhost:8000/
