#!/bin/sh
# Quick script to install PJB standalone server
# with MySQL connector/J
#
BASEDIR=$(dirname $0) 
INSTALL_DIR=$BASEDIR/pjb621
MYSQL_JDBC_VERSION=5.1.36
echo "Installing PJB"
URL="http://downloads.sourceforge.net/project/php-java-bridge/Binary%20package/php-java-bridge_6.2.1/JavaBridgeTemplate621.war?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fphp-java-bridge%2Ffiles%2FBinary%2520package%2Fphp-java-bridge_6.2.1%2F&ts=1415114437&use_mirror=softlayer-ams"
FILE=$INSTALL_DIR/JavaBridgeTemplate.war
if [ ! -f $FILE ]; then
    mkdir -p $INSTALL_DIR
    wget $URL -O $FILE;
    unzip $FILE -d $INSTALL_DIR;
fi
echo "Installing Mysql Connector/J"
LIBDIR=$BASEDIR/pjb621/WEB-INF/lib
MYSQL_JDBC_URL=http://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-java-$MYSQL_JDBC_VERSION.zip
OUTPUT_FILE=$INSTALL_DIR/mysql-connector-java-$MYSQL_JDBC_VERSION.zip
if [ ! -f $OUTPUT_FILE ]; then
    mkdir -p $INSTALL_DIR
    wget $MYSQL_JDBC_URL -O $OUTPUT_FILE
fi
unzip -o -j $OUTPUT_FILE mysql-connector-java-$MYSQL_JDBC_VERSION/mysql-connector*.jar -d $LIBDIR;

