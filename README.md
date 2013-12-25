
# Beanstalk console ![English version](http://upload.wikimedia.org/wikipedia/en/thumb/a/ae/Flag_of_the_United_Kingdom.svg/22px-Flag_of_the_United_Kingdom.svg.png)

*Admin console for [Beanstalk](http://kr.github.com/beanstalkd) queue server, written in PHP*

![Beanstalk Console Screenshot](https://raw.github.com/ptrofimov/beanstalk_console/master/cover/btconsole.png)

**Features**

- Common list of servers in config for all users
- Each user can add its own personal Beanstalkd server
- Full list of available tubes
- Complete statistics about jobs in tubes
- Realtime auto-update with highlighting of changed values
- You can view jobs in ready/delayed/buried states in every tube
- You can add/kick/delete jobs in every tube
- You can select multiple tubes by regExp and clear them

**Installation**

### Use composer (*recommended*)

If you don't have Composer yet, download it following the instructions on http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `create-project` command to generate a new application:

    php composer.phar create-project ptrofimov/beanstalk_console -s dev path/to/install

Composer will install the Beanstalk Cosnole and all its dependencies under the `path/to/install` directory.

### Download an Archive File

[Download](https://github.com/downloads/ptrofimov/beanstalk_console/beanstalk_console.zip), unzip files to your *wwww* directory and launch from *public* directory, enjoy!


**Authors:** Petr Trofimov, Sergey Lysenko, Pentium10

--------------------------------------------------

# Beanstalk консоль ![Русская версия](http://upload.wikimedia.org/wikipedia/en/thumb/f/f3/Flag_of_Russia.svg/22px-Flag_of_Russia.svg.png)

*Административная консоль для сервера очередей [Beanstalk](http://kr.github.com/beanstalkd), написанная на PHP*

**Возможности**

- Общий список серверов в конфиге для всех пользователей
- Каждый пользователь может добавить свой персональный сервер
- Полный список доступных труб
- Полная статистика тасков в трубах
- Realtime-обновление с подсветкой изменившихся значений
- Вы можете просматривать таски в каждой трубе (ready/delayed/buried)
- Вы можете выполнять операции с тасками в каждой трубе (add/kick/delete)

**Установка**

[Скачайте](https://github.com/downloads/ptrofimov/beanstalk_console/beanstalk_console.zip), положите распакованные файлы в www папку и наслаждайтесь!

**Авторы:** Петр Трофимов, Сергей Лысенко, Pentium10

--------------------------------------------------

** Previous version is available [here](https://github.com/ptrofimov/beanstalk_console/tree/1.0)**

*Keywords: beanstalk, beanstalkd, queue, console, gui, admin, web admin, monitoring, stats, interface, php*
