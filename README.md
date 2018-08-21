# Content layer development triple store

Triple storage containing default data to kick-start content layer local development.

The following RDF triples will be imported once the service starts:

- [Corporate body](http://publications.europa.eu/resource/cellar/07e1a665-2b56-11e7-9412-01aa75ed71a1.0001.10/DOC_1)
- [Target audience](http://publications.europa.eu/resource/cellar/4cb35e04-75c5-11e7-b2f2-01aa75ed71a1.0001.08/DOC_1)  
- [Organization type](http://publications.europa.eu/resource/cellar/a8bcd901-17b8-11e8-ac73-01aa75ed71a1.0001.06/DOC_1)  
- [Resource type](http://publications.europa.eu/resource/cellar/07fa8597-2b56-11e7-9412-01aa75ed71a1.0001.10/DOC_1)  

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

Purge all data:

```
$ docker exec triple-store-dev ./vendor/bin/robo purge
```

Import default data:

```
$ docker exec triple-store-dev ./vendor/bin/robo import
```

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