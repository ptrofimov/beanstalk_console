# Beanstalk console ![English version](http://upload.wikimedia.org/wikipedia/en/thumb/a/ae/Flag_of_the_United_Kingdom.svg/22px-Flag_of_the_United_Kingdom.svg.png)

[![Latest Stable Version](https://poser.pugx.org/ptrofimov/beanstalk_console/v/stable.png)](https://packagist.org/packages/ptrofimov/beanstalk_console) [![Total Downloads](https://poser.pugx.org/ptrofimov/beanstalk_console/downloads.png)](https://packagist.org/packages/ptrofimov/beanstalk_console) [![License](https://poser.pugx.org/ptrofimov/beanstalk_console/license.png)](https://packagist.org/packages/ptrofimov/beanstalk_console)

*Admin console for [Beanstalk](http://kr.github.com/beanstalkd) queue server, written in PHP*

![Beanstalk Console Screenshot](https://raw.github.com/ptrofimov/beanstalk_console/master/cover/btconsole.png)

**Features**

- Common list of servers in config for all users + optional Basic Auth
- Global server list can be set via BEANSTALK_SERVERS environment variable
- Each user can add its own personal Beanstalkd server
- Complete statistics about jobs in tubes
- Realtime auto-update with highlighting of changed values
- View jobs in ready/delayed/buried states in every tube
- Highlighting of buried jobs for better visibility
- Add/kick/delete jobs in every tube
- Select multiple tubes by regular expression and clear them
- Saved jobs: Store sample jobs as templates, kick/edit them (useful for development)
- Search within job data fields
- Move jobs between tubes
- Pause tubes
- Prepare review batches by moving jobs into an isolated review tube before moving, duplicating, or deleting them
- Configurable UI settings (auto-refresh, decoding, pause duration, etc.)

**Review batches**

Review batches help inspect a bounded set of jobs without workers changing them during review. From a tube page, click "Prepare review batch", choose the source state, set how many current jobs to review, and choose the review tube name. Ready and delayed jobs can be reviewed when no clients are watching/waiting or when the tube is paused; the dialog can pause the tube and proceed when needed. Buried jobs can be reviewed directly because workers cannot reserve buried jobs.

The review page uses a destination tube field, prefilled with the source tube. Moving jobs to the source tube returns them; changing the destination moves them elsewhere. Existing tubes are suggested, but new tube names can be typed. Selected jobs or all undecided jobs can also be duplicated to the destination or deleted from the review tube. Duplicated jobs remain available for further review actions until their review copy is moved or deleted. Job bodies can be snapshotted to local JSONL during preparation so they remain visible after review copies are moved or deleted.

Review batch files are console-local support files used for auditing, current-state tracking, exports, and optional body display. They are not automatically removed, so delete completed review batches or disable body snapshots if disk growth is a concern.

**Change log**

Navigate to [Releases](https://github.com/ptrofimov/beanstalk_console/releases).


**Installation**

### Use composer (*recommended*)

If you don't have Composer yet, download it following the instructions on http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `create-project` command to generate a new application:

    php composer.phar create-project ptrofimov/beanstalk_console -s dev path/to/install

Composer will install the Beanstalk Console and all its dependencies under the `path/to/install` directory.

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

### Setup using vagrant

Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads) and [vagrant](http://www.vagrantup.com/downloads.html) then run (from project root):

    vagrant up

After provision beanstalk console will be available at [http://localhost:7654](http://localhost:7654) (port could be configured in Vagrantfile)

### Download an Archive File

[Download](https://github.com/ptrofimov/beanstalk_console/archive/master.zip), unzip files to your *www* directory and launch from *public* directory, enjoy!

**Authors:** Petr Trofimov, Sergey Lysenko, Pentium10

--------------------------------------------------

** Previous version is available [here](https://github.com/ptrofimov/beanstalk_console/tree/1.0)**

*Keywords: beanstalk, beanstalkd, queue, console, gui, admin, web admin, monitoring, stats, interface, php*
