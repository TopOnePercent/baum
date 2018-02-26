#!/bin/bash

clear; vendor/bin/phpunit -c phpunit-no-logging.xml --stop-on-failure --stop-on-error
