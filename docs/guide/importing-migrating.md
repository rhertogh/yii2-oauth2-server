Importing/Migrating from other servers
======================================

To ease the transition from other Oauth2 Servers this extension can generate migrations to import existing data.

## Generating and running an import migration

> Note: Make sure the Yii2-Oauth2-Server is fully [installed](start-installation.md).

1. To generate an import migration you can run:  
   `./yii oauth2/migrations/generate-import <origin>`.  
   Where `<origin>` is the ID of an origin project to import. 
     
   Currently, the following projects are supported:

   | ID    | Project URL                                 | Notes                                      |
   |-------|---------------------------------------------|--------------------------------------------|
   | filsh | https://github.com/filsh/yii2-oauth2-server | Only the "clients" table will be imported. |

2. Run the migration:
   * Run your migration command as usual (most likely `./yii migrate`).
   * The migrations generated in step 1 should be ready to be applied.
   * Confirm the application the migrations (or add `--interactive=0` as option to the `migrate` command).
