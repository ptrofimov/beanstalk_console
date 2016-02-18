# Beanstalk console ![English version](http://upload.wikimedia.org/wikipedia/en/thumb/a/ae/Flag_of_the_United_Kingdom.svg/22px-Flag_of_the_United_Kingdom.svg.png)

[![Latest Stable Version](https://poser.pugx.org/ptrofimov/beanstalk_console/v/stable.png)](https://packagist.org/packages/ptrofimov/beanstalk_console) [![Total Downloads](https://poser.pugx.org/ptrofimov/beanstalk_console/downloads.png)](https://packagist.org/packages/ptrofimov/beanstalk_console) [![License](https://poser.pugx.org/ptrofimov/beanstalk_console/license.png)](https://packagist.org/packages/ptrofimov/beanstalk_console)

*Admin console for [Beanstalk](http://kr.github.com/beanstalkd) queue server, written in PHP*

![Beanstalk Console Screenshot](https://raw.github.com/ptrofimov/beanstalk_console/master/cover/btconsole.png)

**Features**

- Common list of servers in config for all users + optional Basic Auth
- Global server list can be set via BEANSTALK_SERVERS environment variable
- Each user can add its own personal Beanstalkd server
- Full list of available tubes
- Complete statistics about jobs in tubes
- Realtime auto-update with highlighting of changed values
- You can view jobs in ready/delayed/buried states in every tube
- You can add/kick/delete jobs in every tube
- You can select multiple tubes by regExp and clear them
- You can move jobs between tubes
- Ability to Pause tubes
- Saved jobs (store sample jobs as a template, kick/edit them, very useful for development)
- Search jobs data field
- Customizable UI (code highlighter, choose columns, edit auto refresh seconds, pause tube seconds)

Change log on [Releases](https://github.com/ptrofimov/beanstalk_console/releases).

**Installation**

### Use composer (*recommended*)

If you don't have Composer yet, download it following the instructions on http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `create-project` command to generate a new application:

    php composer.phar create-project ptrofimov/beanstalk_console -s dev path/to/install

Composer will install the Beanstalk Console and all its dependencies under the `path/to/install` directory.

### Setup using vagrant

Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads) and [vagrant](http://www.vagrantup.com/downloads.html) then run (from project root):

    vagrant up

After provision beanstalk console will be available at [http://localhost:7654](http://localhost:7654) (port could be configured in Vagrantfile)

### Download an Archive File

[Download](https://github.com/ptrofimov/beanstalk_console/archive/master.zip), unzip files to your *www* directory and launch from *public* directory, enjoy!

### Run as a Docker container

Install [Docker](https://docs.docker.com/installation/) then build and run with the following command (from project root):

    docker build --rm -t beanstalk_console .
    docker run -d -p "80:80" --name beanstalk_console beanstalk_console

If you would rather just run the existing automated build of this project, run (from project root):

    docker run -d -p "80:80" -e APACHE_PORT=80 --name beanstalk_console agaveapi/beanstalkd-console

To configure webapp with a custom beanstalk server to load at runtime, set the `BEANSTALKD_HOST` and `BEANSTALKD_PORT` environment variables.

    docker run -d -p 80:80 \
               --name beanstalk_console \
               -e 'BEANSTALKD_HOST=beanstalkd' \
               -e 'BEANSTALKD_PORT=11300' \
               beanstalk_console

To spin up a console with a beanstalkd server all at once, install [Docker Compose](https://docs.docker.com/compose/) and run (from project root):

    docker-compose up

**Authors:** Petr Trofimov, Sergey Lysenko, Pentium10

--------------------------------------------------

# Beanstalk консоль ![Русская версия](http://upload.wikimedia.org/wikipedia/en/thumb/f/f3/Flag_of_Russia.svg/22px-Flag_of_Russia.svg.png)

*Административная консоль для сервера очередей [Beanstalk](http://kr.github.com/beanstalkd), написанная на PHP*

**Возможности**

- Общий список серверов в конфиге для всех пользователей
- Глобальный список серверов может быть установлен через переменную окружения BEANSTALK_SERVERS
- Каждый пользователь может добавить свой персональный сервер
- Полный список доступных труб
- Полная статистика тасков в трубах
- Realtime-обновление с подсветкой изменившихся значений
- Вы можете просматривать таски в каждой трубе (ready/delayed/buried)
- Вы можете выполнять операции с тасками в каждой трубе (add/kick/delete)

**Установка**

[Скачайте](https://github.com/ptrofimov/beanstalk_console/archive/master.zip), положите распакованные файлы в www папку и наслаждайтесь!

**Установка с помощью vagrant**

Установите [VirtualBox](https://www.virtualbox.org/wiki/Downloads) и [vagrant](http://www.vagrantup.com/downloads.html) затем запустите (в корневой директории проекта):

    vagrant up

После завершения провизии консоль будет доступна по адресу [http://localhost:7654](http://localhost:7654) (порт можно сконфигурировать в Vagrantfile)

**Авторы:** Петр Трофимов, Сергей Лысенко, Pentium10

--------------------------------------------------

** Previous version is available [here](https://github.com/ptrofimov/beanstalk_console/tree/1.0)**

*Keywords: beanstalk, beanstalkd, queue, console, gui, admin, web admin, monitoring, stats, interface, php*
