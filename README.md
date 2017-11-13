# Faaast

[![Docker Stars](https://img.shields.io/docker/stars/tplcom/faaast.svg)]() [![Docker Build 
Status](https://img.shields.io/docker/build/tplcom/faaast.svg)](https://hub.docker.com/r/tplcom/faaast/builds/)

## About

A package manager as a service (SaaS).

Or a package software with **npm, pnpm, yarn, ied, gem, composer, bundler, drush, pip etc** using Docker.

## Why

- Because sometimes there unknown errors with package managers.
- Because WiFi issues may corrupt packaging.
- Because sometimes you don't want to spend time for packaging.
- Because not every machine can use packages.
- Because Docker can do this!

## Usage

### Online UI
Try online at [faaast.download](https://faaast.download/?utm_source=github&utm_medium=browser&utm_campaign=github_repo).

### Docker image

If you want to use the docker image ([tplcom/faaast](https://hub.docker.com/r/tplcom/faaast)) on
 your own:

```bash
// Let's assume you want to run "npm install visionmedia/express"
// This will get the "node_packages" under current path "home" folder
docker run -it --rm -w /home -v $(pwd)/home:/home tplcom/faaast npm install visionmedia/express

```

### API

You can get the packaged (tar.gz or zip) files using wget, curl and python as simple as calling the 
simple HTTP api.

```bash
wget $(curl -s "https://faaast.download/faaast.php?cmd=[MY_COMMAND]&id=[RANDOM_20_LETTERS]&compress=tar.gz&api=true" | python -c 'import json,sys;obj=json.load(sys.stdin);print obj["'file'"]';)

# Example

wget $(curl -s "https://faaast.download/faaast.php?cmd=npm+install+webpack&id=ddddddddddeeeeeeeeee&api=1" | python -c 'import json,sys;obj=json.load(sys.stdin);print obj["'file'"]';)

```

### CLI tool

Install the **faaast** command locally to get the packaged files from your command line. 

```bash
wget -q https://raw.githubusercontent.com/theodorosploumis/faaast/master/scripts/faaast && \
chmod +x faaast && \
mv faaast /usr/local/bin/faaast

```

Then run "faaast" command like this:

```bash
faaast "npm install react"
```

## Software per docker image

| Software | Version |
| :---  |:--- |
| bundler | 1.16.0 |
| composer | 1.5.2 |
| drush | 8.1.15 |
| gem | 2.5.1 |
| ied | 2.3.6 |
| node | v8.9.1 |
| npm | 5.5.1 |
| pnpm | 1.21.0 |
| python | 2.7.12 |
| python3 | 3.5.2 |
| pip | 9.0.1 |
| pip3 | 8.1.1 |
| php | 7.0.22 |
| ruby | 2.3.1p112 |
| yarn | 1.3.2 |

## ToDo

See this issue: [ToDo](https://github.com/theodorosploumis/faaast/issues/1).

## License

[![license](https://img.shields.io/github/license/theodorosploumis/faaast.svg)](LICENSE)
