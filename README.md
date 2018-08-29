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

# Beanstalk کنسول 


**ویژگی ها**

- لیست تمامی سرور ها برای نمام کاربران+ورود پایه کاربران
-  BEANSTALK_SERVERS environment لیست سراسری سرور ها ک میتوان قرار داد در متغیر 
-  Beanstalkd خود را به ضورت شخصی وارد کند هر کاربر میتواند سرور 
- لیست کاملی از تونل های سرور 
- لیست کاملی از وضعیت کار ها در تونل
- به روز رسانی سریع با نشان دادن تغییرات
- میتوانید کار ها را در سه صورت رزرو شده - به تاخیر افتاده- از یاد رفته ببینید
- میتوانید هر کار را اضاقه کنید یا اخراج کنید یا پاک کنید
- شما با regularExp میتوانید چندیت تونل را انتخاب و پاک کنیدشان
- شما میتوانید کار ها را بین تونل ها انتقال دهید
- میتوناید تونل ها را متوقف کنید
- ذخیره سازی کار ها(ذخیره سازی ساده کار ها-اخراج کردن یا تغییر دادن ان ها - ک بسیار مفید برای برنامه نویسی است)
- جستجو در کار ها
- شخصی سازی صفحات(براق کردن کد ها - انتخاب ستون ها - تغییر زمان به روز رسانی- متوقف ساختن تونل ها)

**نحوه نصب**

### استفاده composer (*پیشنهاد شده*)

اگر هنوز کمپوزر را نصب نکرده اید از طریق سایت زیر اقدام به نصب ان کنید:


http://www.getcomposer.org

همچنین میتوان از طریق دستور زیر اقدام به نصب کرد :


    curl -s http://getcomposer.org/installer | php

سپس از دستور زیر برای ایجاد پروژه جدید استفاده کنید

    php composer.phar create-project ptrofimov/beanstalk_console -s dev path/to/install


**نحوه نصب نسخه فارسی**:
فایل را از ادرس زیر دانلود کرده و سپس از حالت فشرده خارج کنید

https://github.com/snip77/Persian_beanstalk_console.git
