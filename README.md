# GeoCore Community

GeoCore Community, open source classifieds and auctions software

This is a work in progress, if you stumbled on it now, **Use at your own risk!**

This is not ready for a production environment yet, we are working on converting the formerly licensed software to be
open source as time allows.

## Notes for upgrades and installs

These are **incomplete** - mainly just notes of what we will not want to forget to add in the "official" update
instructions.

* Delete your `addons/log_license_db/` folder and contents, it is no longer needed and may eventually break your site
  if / when updating to PHP8.
* To facilitate checking out the git repo and hosting it directly, `config.php` is not part of the fileset.  It is
  renamed to `config.example.php` and you are meant to copy/rename it to `config.php` at installation.

## Using Docker

**Do not use in production!**  The docker setup is for local testing purposes only.

We have experimental docker set up!  In the instructions below, any command line commands should be run from the base
folder.

To use, first copy the docker config:

```
cp contrib/docker/config.php src/
```
Note: This step may be automated somehow in future, maybe using ENV vars or something.  For now you must copy.

Then build it:
```
docker-compose build
```

The above should only be needed once (or any time we change it significantly, like for new versions of PHP etc).

Once it is done, start up the docker container:
```
docker compose up
```

This one is done any time you reboot.  (You could also use docker desktop to manage)

Also, the first time, you will need to install the software:

http://localhost:8080/setup/

Just run through it like a normal GeoCore installation.

Once it is installed, access the front end at:

http://localhost:8080/

Access admin panel:

http://localhost:8080/admin/

If you need access to the database directly, it sets up adminer (alt. to PHPMyAdmin) so you can access it here:

http://localhost:8083/?server=db&username=geocore-community&db=geocore-community

That link includes the user/pass and everything.  This is why it is only suitable for local testing!
