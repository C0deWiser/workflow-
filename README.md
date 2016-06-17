## Workflow access manager for laravel applications

This package provides support for setting access permissions to different workflow entities based on
the user roles, item states/properties and user-item relationships.

### Installation

#### Preparations

* Require `media101/workflow` into `composer.json` (composer is the default way to install this package);

* Add the service providers `Media101\Workflow\ConsoleServiceProvider` and `Media101\Workflow\WorkflowServiceProvider`
to the `providers` list in your application's `configs/app.php`;

* Run `php artisan vendor:publish` to copy package config into application configurations directory,
then configure it as needed;

* Run `php artisan workflow:tables` to create migrations initializing all the tables needed to store the permissions
and all the other information;

* Execute `php artisan migrate` to apply the migrations.

* Modify you application user model so that it implements `Media101\Workflow\Contracts\RolesOwner` contract, and
include `Media101\Workflow\Traits\RolesOwner` trait (recommended)

This will conclude the preliminary configuration, the following steps will show how to connect some model class
to be used with the workflow access control, and how to configure the permissions.

#### Adding class to the workflow-controlled

For every class you want to use in the workflow, do the following:

* The class must implement `Media101\Workflow\Contracts\WorkflowItem` contract, it is possible to include the
`Media101\Workflow\Traits\WorkflowItem` trait which already implements all the required methods.

* Add the class name into workflow extension config under the key `classes`

* Run command `php artisan workflow:reinit` to initialize permissions and metadata for the new model class.

*The command `workflow:reinit` MUST be run every time you recreate table or drop cache, or pull code with new workflow entities.*

##### Fine-tuning class behavior

In workflow model suggested by this package, each model has list of actions (override `Entity::workflowActions` to
set this list),  features (override `Entity::workflowFeatures`) and relations with user (override `Entity::workflowRelation`).
For each defined feature, e.g `important` method `Entity#isImportant` called on entity to determine whether or not this
entity instance has this feature; for each relation e.g. `manager` method `Entity#isUserManager($user)` must determine
if the user has certain relation with this entity.

#### Configuring permissions

Configuring is not done as a part of this extension, because it would require some kind of interface.
`media101\admin-workflow` is one of the packages which have interface to configure permissions provided with this package.

### Usage

To check user's permission on some item (instance of a class implementing the contract) call the `check` method
(or it's derivative) on the service implementing the `Media101\Workflow\Contracts\Workflow` or standard Laravel `Gate`
contract. Such singleton will already be preconfigured in the laravel's dependencies container, so it can be
type-hinted where appropriate. Also, if your have defined `Gate` facade, you can just call `Gate::allows('action', $item)`.

Eloquent query for the workflow items can be filtered (to only leave the allowed items) by calling the `filter` method
on the before-mentioned service.

### Advanced subjects

#### Default states

If you want instances of your class to be created with some initial state, include the
`Media101\Workflow\Traits\WorkflowDefaultState` trait into your workflow class. By default, the first state returned
by `workflowStates` will be assigned on model creation, but you can override the method `initState` to do anything
you want.
