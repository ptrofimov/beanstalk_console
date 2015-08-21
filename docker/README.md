# Running Beanstalkd Console
================================

This project has been Dockerized for easier deployment and use. Instructions on building and running are given here.

## Building

To build a fresh container, run the following command, substituting whatever tag you would like to give the resulting image.

```
docker build -rm -t beanstalkd-console .
```

## Running Containers

Once built, you can run a container manually using the following command.

```
docker run -d -p 80:80 --name beanstalk_console beanstalk_console
```

If you need a beanstalk server to test against, you can use the public image created by the [Agave](http://agaveapi.co) dev team in the [Docker Registry](https://hub.docker.com/r/agaveapi/beanstalkd), `agaveapi/beanstalkd`. The following command will start a beanstalkd on the host machine and make the the default port available.

```
docker run -h beanstalkd -d -p 11300:11300 beanstalkd
```

## Runtime Configuration

To configure webapp with a custom beanstalk server to load at runtime, set the `BEANSTALKD_HOST` and `BEANSTALKD_PORT` environment variables.

```
docker run -d -p 80:80 \
					 --name beanstalk_console \
					 -e 'BEANSTALKD_HOST=beanstalkd' \
					 -e 'BEANSTALKD_PORT=11300' \
					 beanstalk_console
```

## Logging

By default, all access and error logs will stream to stdout and be available through the Docker Event Stream and `docker logs` command.

## Orchestrating Infrastructure

To make the orchestration process a bit easier, a [Docker Compose](https://docs.docker.com/compose/) file is available. The following command will start both the console and daemon servers ready to use out of the box. The beanstalk server will be available on the default `11300` port. The beanstalkd console will be available at `http://<your docker host>/`.

```
docker-compose up
```
