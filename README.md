# laravel-api-helper

A set of API tools for Laravel projects

# Installation

## Prerequisites

 - PHP7.1+
 - Laravel

## Install via composer

- Run composer command
  - `composer require mpokket/laravel-api-helper`

# Usage

To add `Sunset` and `Deprecation` for your APIs, use the following annotations over the API method directly.

## Annotation options

```php
/**
* @Deprecation(since=true) // If you don't know the date the API will deprecated on
* Deprecation annotation attributes
* since - true or the date and time (optional)
* sunset - Date and time (optional)
* alternate - URL for superseding API
* policy - URL for sunset policy 
*/
```

### Example response header:

> Deprecation: true


# Support

If you require any support, kindly use GitHub issue tracker for this project.

