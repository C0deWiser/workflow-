## Workflow access manager for laravel applications

This package provides support for setting access permissions to different workflow entities based on
the user roles, item states/properties and user-item relationships.

### Installation

#### Preparations

* Require media101/workflow into `composer.json` (composer is the default way to install this package);

* Add the service providers `Media101\Workflow\ConsoleServiceProvider` and `Media101\Workflow\WorkflowServiceProvider`
to the `providers` list in your application's `configs/app.php`;

* Run `php artisan vendor:publish` to copy package config into application configurations directory,
then configure it as needed;

* Run `php artisan workflow:tables` to create migrations initializing all the tables needed to store the permissions
and all the other information;

* Execute `php artisan migrate` to apply the migrations.

This will conclude the preliminary configuration, the following steps will show how to connect some model class
to be used with the workflow access control, and how to configure the permissions.

#### Adding class to the workflow-controlled

#### Configuring permissions
