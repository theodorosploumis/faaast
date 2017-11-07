# Faaast

[![Docker Stars](https://img.shields.io/docker/stars/tplcom/faaast.svg)]() [![Docker Build 
Status](https://img.shields.io/docker/build/tplcom/faaast.svg)](https://hub.docker.com/r/tplcom/faaast/builds/)

## About

A package manager as a service (SaaS).

Or a package software with **npm, pnpm, yarn, ied, gem, composer, bundler, drush, pip etc** using
 Docker.

## Why

- Because sometimes there unknown errors with package managers.
- Because WiFi issues may corrupt packaging.
- Because sometimes you don't want to spend time for packaging.
- Because not every machine can use packages.
- Because Docker can do this!

## Usage

- Try online at [faaast.download](https://faaast.download/?utm_source=github&utm_medium=browser&utm_campaign=github_repo).

- If you want to use the docker image ([tplcom/faaast](https://hub.docker.com/r/tplcom/faaast)) on
 your own:

```bash
// Let's assume you want to run "npm install visionmedia/express"
// This will get the "node_packages" under current path "home" folder
docker run -it --rm -w /home -v $(pwd)/home:/home tplcom/faaast npm install visionmedia/express

```

## Software per docker image

| Software | Version |
| :---  |:--- |
| bundler | 1.16.0 |
| composer | 1.5.2 |
| drush | 8.1.15 |
| gem | 2.5.1 |
| ied | 2.3.6 |
| node | v8.9.0 |
| npm | 5.5.1 |
| pnpm | 1.19.5 |
| python | 2.7.12 |
| pip | 9.0.1 |
| php | 7.0.22 |
| ruby | 2.3.1p112 |
| yarn | 1.2.1 |

## ToDo

See this issue: [ToDo](https://github.com/theodorosploumis/faaast/issues/1).

## License

[![license](https://img.shields.io/github/license/theodorosploumis/faaast.svg)](LICENSE)
