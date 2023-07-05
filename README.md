# Shift8 Jenkins Integration
* Contributors: shift8
* Donate link: https://www.shift8web.ca
* Tags: jenkins, wordpress, wordpress automation, staging wordpress, staging, push, production push, jenkins push, wordpress deploy, wordpress build, build, deployment, deploy
* Requires at least: 3.0.1
* Tested up to: 5.7
* Stable tag: 2.0.12
* License: GPLv3
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

Plugin that allows you to trigger a Jenkins hook straight from the Wordpress interface. This is intended for end-users to trigger a "push" for jenkins to push a staging site to production (for example). For full instructions and an in-dep
th overview of how the plugin works, you can check out our detailed [blog post about this plugin](https://shift8web.ca/2017/12/wordpress-plugin-to-integrate-jenkins-build-api/).

## Want to see the plugin in action?

You can view three example sites where this plugin is live :

- Example Site 1 : [Wordpress Hosting](https://www.stackstar.com "Wordpress Hosting")
- Example Site 2 : [Web Design in Toronto](https://shift8web.ca "Web Design in Toronto")

## Features

- Settings area to allow you to define the Jenkins push URL including the authentication key

## Installation 

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/shif8-jenkins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the plugin settings page and define your settings

## Frequently Asked Questions 

### I tested it on myself and its not working for me! 

You should monitor the Jenkins log to see if it is able to hit the site. Also monitor the server logs of your Wordpress site to identify if any problems (i.e. curl) are happening.

## Screenshots 

1. Admin area

## Changelog 

### 1.00
* Stable version created

### 1.01
* Wordpress 5 compatibility

### 1.02
* Cleanup

### 1.03
* Cleanup again

### 1.04
* Wordpress 5.5 compatibility

### 2.0.1
* Wordpress 5.7 compatibility
* Ability to schedule push trigger with WP Cron
* Updated activity log table schema, cleanup after deactivation, db version tracking
* Centralized activity logging function
* Cleanup wp cron scheduling

### 2.0.2
* Minor CSS style fix for admin interface

### 2.0.3
* Admin setting for api key changed to password input box type

### 2.0.4
* Added activity log entry if scheduled push remote get fails

### 2.0.5
* Adjustment of log entry

### 2.0.6
* Immediately trigger a push for scheduled pushes, but with delay=seconds query string

### 2.0.7
* Minor fix for query string

### 2.0.8
* Minor fix for query string

### 2.0.9
* Minor fix to set timezone in second delay calculation for scheduled pushes

### 2.0.10
* Minor fix to set delay seconds = 0 when immediate push

### 2.0.11
* Wordpress 6.2 compatibility

### 2.0.12
* Adjust readme
