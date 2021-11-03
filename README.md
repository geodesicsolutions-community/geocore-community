# GeoCore Community

GeoCore Community, open source classifieds and auctions software

This is a work in progress, if you stumbled on it now, **Use at your own risk!**

This is not ready for a production environment yet, we are working on converting the formerly licensed software to be
open source as time allows.

# Notes for upgrades and installs

These are **incomplete** - mainly just notes of what we will not want to forget to add in the "official" update
instructions.

* Delete your `addons/log_license_db/` folder and contents, it is no longer needed and may eventually break your site
  if / when updating to PHP8.
* To facilitate checking out the git repo and hosting it directly, `config.php` is not part of the fileset.  It is
  renamed to `config.example.php` and you are meant to copy/rename it to `config.php` at installation.

# Using Docker

**Do not use in production!**  The docker setup is for local testing purposes only.

We have experimental docker set up!

## First time setup for docker:

### 1. First, `cd` into the base folder for the software.

When you use `ls` you should see the `src/` folder and all the files at the base level.  If you are on Windows,
consider using [Github CLI](https://cli.github.com/) and it will include "linux like" command line interface, or
Windows PowerShell may work as well.

### 2. Copy the docker config.php into place

Now, copy the docker config to use the config settings docker needs to connect to the DB:

```
cp contrib/docker/config.php src/
```

Note: no changes are needed to the file itself, just copy it into place.  Keep in mind this is not secure so should
only be used for local test environment, like it mentions at the top docker is not set up for production environment.

### 3. Build the docker container:

Run:
```
docker-compose build
```

Note: this step may be needed any time there are major changes such as updating PHP version and the like.

### 4. Start the docker containers:

Run:
```
docker compose up
```

After this point, you can use Docker Desktop to start up the containers, they should be listed under the
`geocore-community` group.

### 5. Run composer install

Run `composer install` the first time inside the container:

```
docker-compose exec --user www-data web composer install
```

### 6. Run /setup to install in the DB

Run the `/setup` script, visit the URL in a browser:

http://localhost:8080/setup/

Just run through it like a normal GeoCore installation.

### 7. Done!  Now try it out:

Once it is installed, access the front end at:

http://localhost:8080/

Access admin panel:

http://localhost:8080/admin/

## Docker commands

A few things you may need even after the first time you have it set up.

To start up the containers, either use the Docker control panel, or you can run this from the base folder:
```
docker compose up
```

If you see that there are changes to `composer.json` and/or `composer.lock` you will need to re-run composer install.
You can do that by running this from the base folder:
```
docker-compose exec --user www-data web composer install
```

When in doubt, if you do a pull on the repo and see a bunch of changes, run that just to be sure.

## Docker URL's

These will work when all 3 docker containers are up (and you have run through the "first time setup" above).  If they
do not work, try running `docker compose up` from the base folder.

**GeoCore front end:**

http://localhost:8080/

**GeoCore admin:**

http://localhost:8080/admin/

**Note:** Unless you change it, it will be set to the default `admin` / `geodesic` for user and pass.

**Adminer - Direct DB Access: (alt. to PHPMyAdmin)**

http://localhost:8083/?server=db&username=geocore-community&db=geocore-community

That link includes the user/pass and everything.  Remember, this is only suitable for local testing!
