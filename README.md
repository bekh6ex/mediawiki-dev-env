# MediaWiki Development Environment
Set up MediaWiki + Wikibase with Docker!

## Requirements
* Docker and Docker Compose: https://docs.docker.com/compose/install/

## Install
* clone this repository and change into it
* clone MediaWiki into the `web` directory `git clone https://gerrit.wikimedia.org/r/mediawiki/core web` - the `web` directory is the entry point of your development web server
* clone a skin and all of the extensions you need
  * `git clone https://gerrit.wikimedia.org/r/mediawiki/skins/Vector web/skins/Vector`
  * `git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/Wikibase.git web/extensions/Wikibase`
  * `git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/WikibaseLexeme.git web/extensions/WikibaseLexeme`
  * `git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/WikibaseQualityConstraints.git web/extensions/WikibaseQualityConstraints`
* run `docker network create mediawiki`
* figure out port forwarding from the container to your host machine: an easy solution is to change the port from the repo service to something like `"8080:80"`; that way you can access it via `http://localhost:8080/`
* run `docker-compose up` and wait for everything to install
* open the index page of the web server in your browser and set up MediaWiki
  * the database credentials are stated in the `docker-compose.yml` (host is `mysql`)
* download the generated `LocalSettings.php`, add lines you need from `LocalSettings.dist.php` and copy it into the `web` directory
* restart the container to run the database migrations
* done!
