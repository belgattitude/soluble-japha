# CONTRIBUTING


## Recommended workflow

### Setup

1. Setup a [GitHub account](https://github.com/), if you haven't yet.
2. Fork the project (i.e from the github project page). 
3. Clone your own fork

### Test

Setup a local JasperBridgeServer and copy ./phpunit.xml.dist in
./phpunit.xml (edit config as needed). 

Check phpunit works by running 

```shell
$ ./vendor/bin/phpunit
```

### Source modification

1. Create a new branch from master (i.e. feature/24)
2. Modify the code... Fix, improve :)

### Release a P/R (pull request)

1. First ensure the code is clean

```shell
$ composer fix
$ composer check
```
2. Commit/Push your pull request. 

Thanks !!!
   
