# VLSM Sync #

This simple set of scripts can be used to sync [VLSM](https://github.com/deforay/vlsm) data across different VLSM instances or across other applications that support the specified XML format

### How to set up ? ###

* Download latest source code
* Copy it into existing vlsm folder
* Set up the Sync folder (dropbox or any other shared folder) and enter the path in VLSM Configuration
* Ensure that the shared folder has read/write access
* Set up scheduled jobs to do the export/import periodically

### What are the commands for scheduled jobs ? ###

* To export requests : php /path/to/vlsm/vlsm-sync/export.php request
* To export results : php /path/to/vlsm/vlsm-sync/export.php result
* To import requests : php /path/to/vlsm/vlsm-sync/import.php request
* To impot requests : php /path/to/vlsm/vlsm-sync/import.php result


### Who do I talk to? ###

* Repo owner or admin