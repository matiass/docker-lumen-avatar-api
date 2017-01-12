# API AVATAR

## What is it?

This API has the function of serving and managing Avatars.
The service allow to serve the avatars in different sizes and types of image
Upload avatars associated with emails and delete avatars.

## How do I use it?

### FIRST - Clone this repo

```bash
git clone https://github.com/matiass/docker-lumen-avatar-api.git
cd docker-lumen-avatar-api
```

### SECOND - Configuration

To change configuration values, look in the `docker-compose.yml` file and change the `php` container's environment variables. 
You have to modify the following variables with your gmail account data to make it work.

    MAIL_USERNAME: miemail@gmail.com
    MAIL_PASSWORD: mipassword

### THIRD - Docker Setup [IF YOU DO NOT HAVE DOCKER]

### [Docker for Mac](https://docs.docker.com/docker-for-mac/)

### [Docker for Windows](https://docs.docker.com/docker-for-windows/)

### [Docker for Linux](https://docs.docker.com/engine/installation/linux/)

### FOURTH - Build & Run

```bash
sudo docker-compose up -d
sudo docker exec -ti lumen_avatar_php composer install
sudo docker exec -ti lumen_avatar_php php artisan migrate
```
### FIFTH - Enjoy

Avatar API is running!

Do you believe me, don't you?

Go to [TEST SECTION](https://github.com/matiass/docker-lumen-avatar-api/blob/master/README.md#how-do-i-test-it)

## How do i Test it?

```bash
sudo docker exec -ti lumen_avatar_php phpunit --stderr
```
## Methods AVAILABLE

Check the [Avatar_API.raml](https://github.com/matiass/docker-lumen-avatar-api/blob/master/Avatar_API.raml) file

## Stop Everything

```bash
docker-compose down
```

# Inconming Features

- Cache
- A control-script for managing the docker-infrastructure components for API Avatar
- Email queue
