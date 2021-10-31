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
