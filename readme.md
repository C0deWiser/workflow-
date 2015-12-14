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

* Modify you application user model so that it implements `Media101\Workflow\Contracts\RolesOwner` contract, and
include `Media101\Workflow\Traits\RolesOwner` trait (recommended)

* (Optional) You might want to configure workflow facade: add the line like
`'Workflow'  => \Media101\Workflow\Facades\Workflow::class` to the `aliases` array in you `configs/app.php`.
After that, you can use the module like that: `\Workflow::allows('edit', $post)` in your code.
Run the `php artisan ide-helper:generate` if you use the `ide-helper` package to receive IDE support for this new facade.

This will conclude the preliminary configuration, the following steps will show how to connect some model class
to be used with the workflow access control, and how to configure the permissions.

#### Adding class to the workflow-controlled

#### Configuring permissions
