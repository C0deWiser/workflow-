[![Latest Stable Version](https://poser.pugx.org/codewiser/workflow/version)](https://packagist.org/packages/codewiser/workflow)

## Workflow access manager for laravel applications

This package provides support for setting access permissions to different workflow entities based on
the user roles, item states/properties and user-item relationships.

## Compatible Laravel 5.x versions

The package is relevant starting from version 5.* (which corresponds to Laravel releases)

_Versions below 5 are saved for the sake of history; installing them is highly not recommended. 
However, for reference, it is worth noting that 2.* versions is only compatible with Laravel 5.3, 
and 1.* versions existed for use with Laravel 5.2._

## Installation

### Preparations

* Require `codewiser/workflow` into `composer.json` (composer is the default way to install this package);

* Add the service providers `Codewiser\Workflow\ConsoleServiceProvider` and `Codewiser\Workflow\WorkflowServiceProvider`
to the `providers` list in your application's `configs/app.php`;

* Run `php artisan vendor:publish` to copy package config into application configurations directory,
then configure it as needed;

* Run `php artisan workflow:tables` to create migrations initializing all the tables needed to store the permissions
and all the other information;

* Execute `php artisan migrate` to apply the migrations.

* Modify you application user model so that it implements `Codewiser\Workflow\Contracts\RolesOwner` contract, and
include `Codewiser\Workflow\Traits\RolesOwner` trait (recommended)

This will conclude the preliminary configuration, the following steps will show how to connect some model class
to be used with the workflow access control, and how to configure the permissions.

### Adding class to the workflow-controlled

For every class you want to use in the workflow, do the following:

* The class must implement `Codewiser\Workflow\Contracts\WorkflowItem` contract, it is possible to include the
`Codewiser\Workflow\Traits\WorkflowItem` trait which already implements all the required methods.

* Add the class name into workflow extension config under the key `classes`

* Run command `php artisan workflow:reinit` to initialize permissions and metadata for the new model class.

*The command `workflow:reinit` MUST be run every time you recreate table or drop cache, or pull code with new workflow entities.*

#### Fine-tuning class behavior

In workflow model suggested by this package, each model has list of actions (override `Entity::workflowActions` to
set this list),  features (override `Entity::workflowFeatures`) and relations with user (override `Entity::workflowRelation`).
For each defined feature, e.g `important` method `Entity#isImportant` called on entity to determine whether or not this
entity instance has this feature; for each relation e.g. `manager` method `Entity#isUserManager($user)` must determine
if the user has certain relation with this entity.

### Configuring permissions

Configuring is not done as a part of this extension, because it would require some kind of interface.
A package that has an interface for setting permissions in development.

### Usage

To check user's permission on some item (instance of a class implementing the contract) call the `check` method
(or it's derivative) on the service implementing the `Codewiser\Workflow\Contracts\Workflow` or standard Laravel `Gate`
contract. Such singleton will already be preconfigured in the laravel's dependencies container, so it can be
type-hinted where appropriate. Also, if your have defined `Gate` facade, you can just call `Gate::allows('action', $item)`.

Eloquent query for the workflow items can be filtered (to only leave the allowed items) by calling the `filter` method
on the before-mentioned service.

You can protect access on whole Laravel resource by using the `Codewiser\Admin\Http\Middleware\WorkflowMiddleware` middleware.

## Advanced subjects

### Default states

If you want instances of your class to be created with some initial state, include the
`Codewiser\Workflow\Traits\WorkflowDefaultState` trait into your workflow class. By default, the first state returned
by `workflowStates` will be assigned on model creation, but you can override the method `initState` to do anything
you want.
