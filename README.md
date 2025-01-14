# Content layer development triple store

Triple storage containing default data to kick-start content layer local development
based on [OpenLink Virtuoso](https://virtuoso.openlinksw.com).


The following RDF triples will be imported once the service starts:

- [Corporate body](http://publications.europa.eu/resource/distribution/corporate-body/20190220-0/rdf/skos_ap_act/corporatebodies-skos-ap-act.rdf)
- [Corporate body classification](http://publications.europa.eu/resource/distribution/corporate-body-classification/20180926-0/rdf/skos_core/corporate-body-classification-skos.rdf)
- [Target audience](http://publications.europa.eu/resource/cellar/4cb35e04-75c5-11e7-b2f2-01aa75ed71a1.0001.08/DOC_1)  
- [Organization type](http://publications.europa.eu/resource/cellar/a8bcd901-17b8-11e8-ac73-01aa75ed71a1.0001.06/DOC_1)  
- [Resource type](http://publications.europa.eu/resource/cellar/07fa8597-2b56-11e7-9412-01aa75ed71a1.0001.10/DOC_1)
- [Place](http://publications.europa.eu/resource/distribution/place/20181212-0/rdf/skos_core/places-skos.rdf)
- [Public event type](http://publications.europa.eu/resource/distribution/public-event-type/20180926-0/rdf/skos_core/public-event-type-skos.rdf)
- [EuroVoc Thesaurus](http://publications.europa.eu/resource/cellar/9f2bd600-ae7b-11e7-837e-01aa75ed71a1.0001.09/DOC_1)  

New default content can be added to [`robo.yml`](./robo.yml) as shown below:

```
data:
  - name: "corporate-body"
    graph: "http://publications.europa.eu/resource/authority/corporate-body"
    url: "http://publications.europa.eu/resource/cellar/07e1a665-2b56-11e7-9412-01aa75ed71a1.0001.10/DOC_1"
    format: "rdf"
```

Value of `format:` property can be either `rdf` or `zip`. If `zip` then an additional `file:` property is expected
containing the archived RDF file name as shown below:

```
data:
  - name: "eurovoc-thesaurus"
    graph: "http://publications.europa.eu/resource/dataset/eurovoc"
    url: "http://publications.europa.eu/resource/cellar/9f2bd600-ae7b-11e7-837e-01aa75ed71a1.0001.09/DOC_1"
    file: "eurovoc_in_skos_core_concepts.rdf"
    format: "zip"
``` 

## Build and run

Build:

```
$ docker build . -t openeuropa/triple-store-dev
```

Run:

```
docker run --name=triple-store-dev -p 8890:8890 openeuropa/triple-store-dev
```

Visit the RDF storage at: http://localhost:8890

## Available commands

Fetch remote data:

```
$ docker exec triple-store-dev ./vendor/bin/robo fetch
```

Purge all data, to be ran before `import`:

```
$ docker exec triple-store-dev ./vendor/bin/robo purge
```

Import default data:

```
$ docker exec triple-store-dev ./vendor/bin/robo import
```

All commands above accept the following options:

```
--import-dir[=IMPORT-DIR]        Data import directory. [default: "./import"]
--host[=HOST]                    Virtuoso backend host. [default: "localhost"]
--port[=PORT]                    Virtuoso backend port. [default: 1111]
--username[=USERNAME]            Virtuoso backend username. [default: "dba"]
--password[=PASSWORD]            Virtuoso backend password. [default: "dba"]
```

Passing these options to a command will override their related default value set in `robo.yml`.

Option values can also be set using the following environment variables:

```
IMPORT_DIR
DBA_HOST
DBA_PORT
DBA_USERNAME
DBA_PASSWORD
```

Default values set via environment variables will override values set in `robo.yml`.

## Working with Docker Compose

In Docker Compose declare service as follow:

```
version: '2'

services:
  triple-store:
    image: openeuropa/triple-store-dev
    ports:
      - 8890:8890
```

For more information about Docker Compose configuration check the parent Docker image
[Tenforce Virtuoso](https://hub.docker.com/r/tenforce/virtuoso/).

In order to test a specific branch of the `triple-store-dev` image follow the steps below:

In the `docker-compose.yml` of the testing project (i.e. the `oe_content` module) use:

```
  sparql:
    build: /path/to/your/local/triple-store-dev/checkout
#    image: openeuropa/triple-store-dev
    environment:
```

Given that all your services are down, to rebuild run the following:

```
docker-compose build --force-rm --no-cache sparql 
docker-compose up -d
```
