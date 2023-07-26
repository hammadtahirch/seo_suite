# What is this?
This is the Shopify start-up app kit for newcomers. By using this demo app they can build a Shopify app using Laravel Mongodb and Vue js and the developer can get a better idea of how Shopify public apps work. hope helpful others.

## Configure locally
you can operate this app on your locally to add more functionality using Shopify admin APIs. we use a docker container to use this app.
## Docker Information

I created a very nice docker setup for this app. It's composed of 3 containers.
- seo_suite_app
- seo_suite_webserver
- seo_suite_mongodb
- seo_suite_redis
you can change these container names by editing the file `docker-compose.yml` on the root.

## Setup instructions

Prerequisite:
Make sure the below tool should be on your local.

    composer 1/2
    Php 8+

Repository and Docker setup

    mkdir seo_suite
    cd seo_suite
    git@github.com:hammadtahirch/seo_suite.git
    composer install
    docker-compose up -d --build

Give some permission to the storage folder and create a `.env` file using the following command.

    chmod -R 0777 storage
    mv "example.env" ".env"

You should now have access to http://localhost:80

get your Shopify credentials from the Shopify app from the Shopify partners store and add them to the env file.
Please don't forget to set up a callback URL in the Shopify app.
For HTTPS, I use the free version of ngrok tunnel forwarding. you can use any solution for it.

    Note: If need any questions feel free to ask.My contact info hammad.tahir.ch@gmail.com and you can catch on linked in 
    www.linkedin.com/in/hammadtahirch
    Due to a shortage of time, I can work on unit tests and some use full comments on function but in future, I'll try to add.