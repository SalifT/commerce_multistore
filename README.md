Commerce Multistore
===================

Implements Drupal Commerce 2.x multiple stores/store owners model.

## Setup

1. Install the module.

2. Assign *Multistore owner* role to a user.

3. Log in as the *Multistore owner* user and visit [user/ID/stores](#) tab.

4. Press the *Add store* button and add a store of any type available.

5. After the store is created visit [store/ID/products](#) tab.

6. Press the *Add product* button and add a couple of products of any type available.

7. Revisit the [user/ID/stores](#) tab and click the store created earlier.

8. On a [store/ID](#) view page click the [store/ID/products](#) tab.

9. Administer the products created earlier.

10. Repeat 2-9 steps for another user and notice that the new user has no access
to the stores and edit access to the products created by previous user.

## Features

The *Multistore owner* by default has the same permissions as an anonymous user
plus permissions to create and update own stores of any type. You may add
*Delete own stores* permission for an owner if you need it in your set up.
Additionally, the owner has permissions to create, update and delete own
products of any type. You can edit these and other permissions for the
*Multistore owner* role by visiting [admin/people/permissions/commerce_multistore_owner](#)
page. There is a *Multistore admin* role which can be assigned to perform this
and other administering tasks. This role has the same permissions as the *user
1* except users, text formats, modules, themes handling and some other
important tasks. Think of it as a subadmin of the site.

If a *Multistore owner* attempts to add a product without any store created then
they will be presented with the same message as the site's admin:

**Products can't be created until a store has been added. [Add a new store.](#)**

The difference is that when clicking the link admin will see a list of all store
types but a regular *Multistore owner* will see only those which are enabled to
create for this role. If *Multistore owner* is not allowed to create any store
then they'll see a friendly message instead of *Access is denied* one.

Furthermore, if the current user is admin and for some reasons there is no one
store type available then a message with a link to create store type will be
shown.

On product creation form a user can only assign it to own stores. At the same
time admin can add a product to any store. Though visiting [user/ID/stores](#)
tab the admin will see only those stores which are owned by them. So, to
administer the site's stores they still require to visit [admin/commerce/config/stores](#)
page.

A regular *Multistore owner* cannot change their store owner or product author.
Only an admin has access to the *Owner* and *Author* autocompletion fields.

The module implements different default store model. Each regular store owner
has their own default store in the set of stores belonging to them. At the same
time the currently existing on the Drupal Commerce module default store is
dimmed as the global default store. So, there might be infinite number of
default stores on a site. Just one rule to remember: being logged as admin can
change the global default store. To change an owner's default store you should
be logged in as this owner. Again, in the context of a store owner the default
store will always be resolved to their own default store, not the global one.

Though not being able to change an owner's default store an admin can overview
their default stores and the global default store on the [admin/commerce/config/stores](#)
page. Also, there you'll find *Mark as default store* VBO action to change the
global default store. Owners can overview their default store on the [user/ID/stores](#)
page. Also, there is a link on a non-default store form page leading to the
current owner's or a global default store in the case when the form is viewed by
admin.

The purpose of a default store is explained for an owner in the description of
the appropriate field on a store form. The possible use case of an owner's
default store for developers might be seen in the
**MultistoreProductDefaultStoreResolver.php** file. When a product is viewed
then the default store is always resolved to a product creator's store.
Remember, with the current module products can be created and added only to
stores owned by this product creator. The filtering of an owner's stores happens
on the query level using the current user as the context from which to load
available stores for a user having "view own commerce_store" permission.
Obviously, that in the context of admin all the stores will be fetched as admins
have the "administer commerce_store" permission. To fetch only a particular
store owner stores in the context of admin you may try this:

```
/** @var \Drupal\commerce_multistore\StoreStorageInterface $storage */
$storage = \Drupal::entityTypeManager()->getStorage('commerce_store');

/** @var \Drupal\Core\Session\AccountInterface $owner */
$owner_stores = $storage->loadMultiple($ids = NULL, $owner);
```

Or, you may fetch a default store for this particular owner:

```
/** @var \Drupal\commerce_multistore\StoreStorageInterface $storage */
$storage = \Drupal::entityTypeManager()->getStorage('commerce_store');

/** @var \Drupal\Core\Session\AccountInterface $owner */
$owner_default_store = $storage->loadDefault($owner);
```
Another useful feature of the module is that when a customer adds products from
different stores to a Shopping cart and then goes to [cart](#) page then they
will be presented with multiple carts each having the involved store name as the
title.

Also, though not strictly related to the multistore model, yet the following
might be found as useful features:

When viewing a *product*, a *store*, a *user*, a *node* or a *profile* you may
use the viewed entity owner ID as contextual filter for a view to display any of
the entities owned by them. Currently, it is only feasible for *node* and *user*
entities. For example, you may display some fields of the products and profiles
in the sidebar when viewing any of the entities listed above. How to:

Create a view listing of some of the entities in the block and then add this
entity contextual filter. In the section *WHEN THE FILTER VALUE IS NOT IN THE URL*
of the filter settings choose *Provide default value* radio button and then
choose *User ID from route context* in the dropdown. Do not forget to check the
*Also look for a node and use the node author* checkbox.

One more feature is that the variations might be filtered by price in views,
both internally and with exposed to a customer filter. For example, you may list
variations of all products on a page and then filter them to display only those
which have less than 9.99 price value.

When viewing this module's *Administer Stores* view on a [store/ID/products](#)
page you'll find the variations next to a product title displayed in a compact
details element which could be expanded to view a list of the product variations.
That is another feature added by the module. You may use this configurable
referenced entity labels' list formatter for any similar entity, not only
product variation labels. Both in views and entity display view.

A regular *Multistore owner* can see product type labels on a products view page
which is not possible for now on the latest Drupal Commerce version.

Obviously that for fully functional multistore site a lot features have to be
added. For example, the task of making possible for a regular store owner to
administer their own payment gateways. That work is started in the
[Commerce Store Gateways](https://github.com/bojanz/commerce_store_gateways)
module. If you install the module then the [store/ID/Payment gateways](#) tab
will be displayed in a row of a store administering tabs.
