# Refactoring Notes

The aim of this refactoring is to update the code to work against the current version of PHP ([`8.4`](https://www.php.net/downloads.php)) and all future versions.

The first step in doing this upgrade was to get a development environment up and running, which for my purpose was Apache with PHP8.3 and MySQL 5.4 orchestrated by Docker Compose. This was done in the `docker` directory.

This environment also includes xDebug for debugging, which has already been configured to work plug-and-play with an IDE such as PHPStorm/VSCode.

In addition to bringing the code up to date with the latest version of PHP, I will also be solving all bugs as listed in `docs/known-bugs` and todo items as listed in `docs/todo`.

## Known Bugs
The following bugs are listed as known in `docs/known-bugs`, they shall be dealt with as time permits.

- [ ] BUG: KB-0001: When resetting a password successfully, it sends a second email (it shouldn't) with a broken `[link]` replacement.
- [ ] BUG: KB-0002: The new player signup needs to have an activation step before you can play.
- [x] BUG: KB-0003: Creating a new team fails
  > **INFO** I stumbled upon this bug while play testing the game when I first got it working on my local machine. The initial fault was fixed in `e5c16693` and was due to the fact that `$teamdesc` wasn't getting set from the `$_POST` array. In addition to that in commit `5cf24d43` I renamed `contnue` statements to `break` within a switch statement because it had broken when running against PHP7.4. Finally, in commit `191fa84d` I replaced usage of a missing `is_team_owner` function call with code that checks the player is the owner of the team they are viewing.
- [ ] BUG: KB-0004: `lrscan`, images are not sourced from the template
- [ ] BUG: KB-0005: `traderoutes`, create a new port<->port traderoute does not work
- [ ] BUG: KB-0006: Admin, user editor, forces a password change even if you do not enter one, and the changed password isn't accessible (encrypted).
- [ ] BUG: KB-0007: Settings page is riddled with errors

## Existing Todo Items
Within `docs/todo` there are listed a number of items that the previous developers wanted to address. These nicely match up with what I would like to achieve with my refactoring. All TODO items will be addressed as time permits, but I have picked a few below that are in line with my **Upgrade Path** goals.

### Refactor Database Service
Refactoring `classes/Db.php` into a database service is a high priority but given its size and complexity it will take some time to complete.

Of the existing `TODO` items the following are relevant to this refactoring:

 - [ ] Switch common and footer to use pdo only for all its calls
 - [ ] Convert all SQL calls from adodb to PDO
 - [ ] Review schema for improvements (BIGINT, more indexes, reduce unstructured data, etc)
 - [ ] Audit all SQL calls to ensure they use row & value style calls, and also use a debug call
 - [ ] Split ships table from player table
 - [ ] Postgresql compatibility

I'm certain about all except for **PostgresSQL compatibility**. I'm not sure if it's worth the effort to support multiple databases without using an **ORM**. It is something I'm open to considering, but given that the application is entirely SQL statements currently, it would be a significant undertaking to refactor everything into an ORM.

#### Convert all SQL calls from AdoDB to PDO
Removing the dependency on AdoDB will allow the use of PHP's PDO driver for connecting with MySQL. On the surface this feels like a small undertaking, but `$db` and `$pdo_db` have been used interchangeably throughout the code base.

- [x] Refactor `Db.php` class into a static database service
- [x] Deprecate `Db::logDbErrors`, global exception handling will handle this in later refactoring
- [x] Refactor usage of `$prefix` to use `Db::table('...')` helper method
- [ ] Refactor all usage of `$db` (~1,300 according to a quick find) to use static method on `Db` class
- [ ] Refactor all usage of `$pdo_db` (~300 according to a quick find) to use static method on `Db` class

There is some interesting mixed-usage of `$db` and `$pdo_db` throughout the code base. For example, several methods in `Traderoute` accept both `$db` and `$pdo_db` as parameters because they make use of classes that use one or the other.

### Migrate all files to use templates
This is listed as a feature request; however, there is also a separate todo for converting `main.php` to be template driven. 

- [ ] Convert main to be template driven
- [ ] Migrate all files to use templates

This is almost as big an undertaking as refactoring the database service. I can see that the original developers made a good effort to separate the presentation from the business logic, but it's not a complete separation.

The application makes use of the [Smarty Template Engine](https://www.smarty.net/) which I haven't much experience with but shall continue using.

#### Refactoring Templates
This task encompasses converting `main.php` to being template driven. However, I shall begin with `index.php` as it's the first page that a player sees and while quite simple on the surface, it's a good place to start.

As with most tasks, when you dig beneath the surface of the code, you find that it's not quite as simple as you might think.

For example, `index.php` has a dependency upon: `common.php`, `classes/Translate.php`, `classes/Languages.php` and `footer_t.php`. Each of these has their own dependencies, and so on.

There is duplication of code in `index.php` and `common.php` relating to language selection. I'm using **Tuppence** to handle the routing and so can create middleware to handle language selection.

The `footer_t.php` file is used by a number of pages to define shared variables for the pages such as player online count, etc. This is the perfect candidate for where to begin refactoring.

- [x] Have `Reg` class be loaded into IoC as config container and available via `config(...)` helper function
- [x] Refactor `footer_t.php`
  - [x] Create helper function for `bnttimer` for the elapsed load time
  - [x] Update `PlayersGateway` to be aware of `Db` refactoring
  - [x] Update `SchedulerGateway` to be aware of `Db` refactoring
  - [x] Update `NewsGateway` to be aware of `Db` refactoring
  - [x] Create a `view` helper function to render templates, passing in the common values set in `footer_t.php`

**Common values from `footer_t.php`**:

- `cur_year` this year
- `footer_show_debug` passed through from `Bnt->footer_show_debug`
- `mem_peak_usage` calculated from `memory_get_peak_usage()`
- `elapsed` processing time in seconds
- `sf_logo_*`, there are four values for the Stack Forge logo, these change depending on the type of page:
  - `sf_logo_link`
  - `sf_logo_width`
  - `sf_logo_height`
  - `sf_logo_type`
- `players_online` value obtained from `PlayersGateway`
- `update_ticker` array of values from `SchedulerGateway`

#### Refactoring Authentication: Login
This section relates to my refactoring of `login2.php`. Interestingly, the login action checks to see if the player ship has been destroyed and at this point does two checks: the first to see if the player has an escape pod installed and the second to see if the player is new to the game. If so, they are spared the game over and end up on a new basic ship having only lost the resources that were stored within the ship they were flying.

It seems odd to do this on login, when elsewhere in the code we have `Ship::isDestroyed` which only checks to see if the ship has an escape pod installed. During refactoring of this section, I shall be having the player ship state set when it's destroyed, not on login. Then on login if their ship was destroyed while they were offline the player will be in a new ship or be redirected to the game over screen explaining that they need to create a fresh character.

- [ ] Move `classes/Login.php` -> `src/Http/Middleware/AuthMiddleware.php` and convert into Middleware
- [x] Move `classes/CheckBan.php` -> `src/Repositories/BanRepository.php` and convert into Repository
- [ ] Move `login2.php` -> `src/Http/Controllers/Auth/LoginController.php` and convert into Controller
- [x] Move `classes/Player.php` -> `src/Models/Player.php` and convert into Model
- [x] Move `classes/Ship.php` -> `src/Models/Ship.php` and convert into Model
- [x] Handle Game Closure (via `$bntreg->game_closed`)
- [ ] Create a fitting template for `auth/player-banned.tpl`, this needs some thought 

As part of this refactoring while converting `classes/CheckBan.php` into `BanRepository` I came to realise that the `bnt_bans` table wasn't being used. The separate `bnt_ip_bans` table is used by `admin/bans_editor.php` but I couldn't find any code that used it (although maybe I missed something). In any case, this has meant I am now adding a new refactoring task to overhaul the player moderation system.

#### Refactoring Authentication: Login Notifications
There are a number of different notification end states that can occur during login. These include:
- Player ship destroyed with escape pod
- Player ship destroyed but new to the game
- Player ship destroyed but not new to the game (Game Over)
- Player Banned

The original game would output a sentence or two to explain what had happened, for example:
> You have died in a horrible incident, [here] is the blackbox information that was retrieved from your ships wreckage.

I think it would be a good idea to have a *notification template* that can be used to display these messages and maybe even commission some art work for each of the different states. I think it would be cool to see ship wreckage showing what happened.

I initially created `auth/player-banned.tpl` but I think that can be replaced with a `notification.tpl` template that can accept a header image and a message and potentially buttons to take the player to different pages.

#### Refactoring Authentication: Password Reset

...

#### Refactoring Authentication: Signup

...

#### Refactoring Authentication: Player Moderation / Bans
As part of refactoring the login system I built the `BanRepository` class and soon came to realise that the table it uses isn't being used by `admin/bans_editor.php` and I can't find any code that creates or updates bans. I shall therefore be overhauling the player moderation system.

- [ ] Remove schema for `bnt_ip_bans`
- [ ] Refactor schema for `bnt_bans` to add a `not_after` column

#### Refactoring Translations / Locale
To refactor templates, I need to also refactor how translations are handled. I will be creating `LocaleMiddleware` to handle language selection, but also need a way of loading the translations from the database. The existing `Translate` class is functional but can be improved. Instead of passing a list of `$categories` to it, I want to
instead be able to call `Translate::get('category', 'key')` and have it return the translation for the given category and key. 

- [ ] Create `LocaleMiddleware` to handle language selection, listens to a GET parameter and persists it in session
- [ ] Create `Translate::get` method
- [ ] Create `lang()` helper function

### Refactoring Reg
`classes/Reg.php` is used as a global registry for the game configuration loading it from the database if existing or else from an ini file.

- [ ] Eliminate all `reg_globals` equivalent hacks
- [ ] Refactor `Reg` class into a static Property Bag
- [ ] Refactor all usage of `$bntreg` to use `Reg` class directly.

I'm unsure what *"Eliminate all `reg_globals`"* means; it may have already been completed but I shall keep it in mind while I am refactoring the `Reg` class.

### Implement Post→Redirect→Get pattern
Part of **migrating all files to use templates** will be detangling markup from business logic. I am a fan of the MVC pattern, and I think that refactoring the application to use a central router with controllers and views would be a good idea.

I have previously written [Tuppence](https://github.com/photogabble/tuppence) a micro-framework for PHP that I have used in other projects. I think that it would be a good idea to use that as a starting point for this refactoring. This is because it provides a powerful PSR-11 dependency injection container, a fast PSR-7 router supporting PSR-15 middleware and a simple and effective PSR-14 event dispatcher all provided by **The League of Extraordinary Packages**.

- [x] Install Tuppence
- [ ] Implement Post→Redirect→Get pattern

Once the application is to the point where all files have been converted to use templates for presentation and controllers for business logic, the Post→Redirect→Get pattern will naturally be implemented.

## Upgrade Path

- [x] Update Dockerfile to run PHP8.4 and enable debugging via creating a `dev` file within the project root so PHP is noisy about warnings and deprecations.
- [ ] Deal with all PHP errors of the type: **Deprecated** and **Warning** as a priority. PHP 8.5 is being released soon, and I think a number of the Deprecations will be removed.


### Creation of Dynamic Properties
In some cases this is where the classes appear to have originated from PHP4, or otherwise where the code is questionably structured. Where class properties are missing, they should be added. However, in some cases such as in `classes/Db.php` and `classes/Reg.php` a Factory or Property Bag is a more appropriate pattern.

- [x] Refactor `classes/Reg.php` to be a Property Bag
- [x] Refactor `classes/Db.php`, the game appears to maintain two connections to the database, one using PHP's PDO and another using AdoDB. There is a TODO from 2013 in `docs/todo` to *"Convert all SQL calls from adodb to PDO"*. This isn't a small amount of work but needs to be done.
