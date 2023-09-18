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

symfony console doctrine:migrations:migrate --env="test"

run all tests from container:

symfony php bin/phpunit


Accounts app API provides the following endpoins and functionality:

    Welcome page.

    Get all clients.

    Given a client id return list of accounts (each client might have 0 or more accounts
    with different currencies).

    Given an account id return transaction history (last transactions come first) and
    support result paging using “offset” and “limit” parameters.

    Transfer funds between two accounts identified by ids.

    Currency conversion takes place when transferring funds between accounts with
    different currencies. Rates acquired from "https://api.exchangerate.host/latest"

Non functionale features:

    Some tests are implemented.
    
    Web service is resilient to 3rd party service unavailability.

    DB schema versioning.