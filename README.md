# DBMigration
 
Instrucciones

Ejecutar `composer install` dentro de la carpeta principal.

Editar el archivo `.env` acorde a la base de datos:

    DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
->

    DATABASE_URL=mysql://usuario:123pass@servidor.local:3306/prueba

Para correr la migracion:
`php bin/console doctrine:migration:execute UniqueIds`

Para ver los cambios a ejecutar:

# DBMigration
 
Instrucciones

Ejecutar `composer install` dentro de la carpeta principal.

Editar el archivo `.env` acorde a la base de datos:

    DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
->

    DATABASE_URL=mysql://usuario:123pass@servidor.local:3306/prueba

Para correr la migracion:
`php bin/console doctrine:migration:execute UniqueIds --dry-run`

Para generar un archivo SQL de la migracion:

# DBMigration
 
Instrucciones

Ejecutar `composer install` dentro de la carpeta principal.

Editar el archivo `.env` acorde a la base de datos:

    DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
->

    DATABASE_URL=mysql://usuario:123pass@servidor.local:3306/prueba

Para correr la migracion:
`php bin/console doctrine:migration:execute UniqueIds --write-sql`

AVISO EL USUARIO DE LA BASE DE DATOS DEBE TENER EL PERMISO DE CREAR, EDITAR Y BORRAR TABLAS E INDICES

Al ejecutar la migracion se creara una tabla dentro de la base de datos llamada **migration_versions** esta es necesaria para la correcta ejecucion de la migracion.