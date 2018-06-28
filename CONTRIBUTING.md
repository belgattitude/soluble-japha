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

### Optional: db install

```
mysql -u <user> -p -e 'create database phpunit_soluble_test_db'
zcat test/data/mysql/schema.sql.gz | mysql -u <user> -p phpunit_soluble_test_db
zcat test/data/mysql/data.sql.gz | mysql -u <user> -p phpunit_soluble_test_db
```
Then activate jdbc tests in `phpunit.xml`


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
   

## Keeping your fork up to date

Best doc [https://help.github.com/articles/syncing-a-fork/](https://help.github.com/articles/syncing-a-fork/)

```
git remote add upstream git://github.com/belgattitude/soluble-japha.git
git fetch upstream
```
### Updating your fork from original repo to keep up with their changes:

```
git checkout master
git merge upstream/master
```
### Push 

```
git push
```
