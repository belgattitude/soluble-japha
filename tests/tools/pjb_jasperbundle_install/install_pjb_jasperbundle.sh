#!/bin/sh
# Quickly create a war file bundle with :
#
#   - MySQL connector J
#   - PHP Java Bridge
#   - Jasper reports
#
# @author Vanvelthem Sébastien

#
# Edit configuration here
#
BASEDIR=$(dirname $0)
JASPER_VERSION=6.1.0
PJB_VERSION=6.2.1
MYSQL_JDBC_VERSION=5.1.36
INSTALL_DIR=$BASEDIR/downloads
DIST_DIR=$BASEDIR/dist/WEB-INF/lib
WAR_CONFIG_DIR=$BASEDIR/config/JavaBridge

# Ensure DIST_DIR exists
echo "Ensure directory $DIST_DIR exists"
if [ ! -d $DIST_DIR ]; then
   mkdir -p $DIST_DIR
fi

echo "Remove older resources"
# Clean older dist
rm $DIST_DIR/*.jar

# Copy war configuration
echo "Preparing war configuration"
cp -rf $WAR_CONFIG_DIR/* $DIST_DIR/../../

# STEP 1 Install mysql connector
echo "Download and install Mysql Connector/J"
MYSQL_JDBC_URL=http://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-java-$MYSQL_JDBC_VERSION.zip
OUTPUT_FILE=$INSTALL_DIR/mysql-connector-java-$MYSQL_JDBC_VERSION.zip
if [ ! -f $OUTPUT_FILE ]; then
    mkdir -p $INSTALL_DIR;
    wget $MYSQL_JDBC_URL -O $OUTPUT_FILE;
fi
unzip -o -j $OUTPUT_FILE mysql-connector-java-$MYSQL_JDBC_VERSION/mysql-connector*.jar -d $DIST_DIR;

# STEP 2 Install Jasper reports jar's
echo "Download and install jasper reports"
JASPER_URL="http://downloads.sourceforge.net/project/jasperreports/jasperreports/JasperReports%20$JASPER_VERSION/jasperreports-$JASPER_VERSION-project.tar.gz?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fjasperreports%2Ffiles%2Fjasperreports%2FJasperReports%2520$JASPER_VERSION%2F&ts=1429189828&use_mirror=softlayer-ams"
OUTPUT_FILE="$INSTALL_DIR/jasperreports-$JASPER_VERSION-project.tar.gz"
if [ ! -f $OUTPUT_FILE ]; then
    mkdir -p $INSTALL_DIR
    wget $JASPER_URL -O $OUTPUT_FILE;
fi
tar -zxvf $OUTPUT_FILE --strip-components=2 --directory $DIST_DIR --wildcards "jasperreports-$JASPER_VERSION/lib/*.jar"
tar -zxvf $OUTPUT_FILE --strip-components=2 --directory $DIST_DIR --wildcards "jasperreports-$JASPER_VERSION/dist/*.jar"

# STEP 3 Install php java bridge
echo "Download and install PHP Java Bridge"
PJB_URL="http://downloads.sourceforge.net/project/php-java-bridge/Binary%20package/php-java-bridge_$PJB_VERSION/JavaBridgeTemplate621.war?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fphp-java-bridge%2Ffiles%2FBinary%2520package%2Fphp-java-bridge_$PJB_VERSION%2F&ts=1415114437&use_mirror=softlayer-ams"
OUTPUT_FILE=$INSTALL_DIR/JavaBridgeTemplate-$PJB_VERSION.war
if [ ! -f $OUTPUT_FILE ]; then
    mkdir -p $INSTALL_DIR
    wget $PJB_URL -O $OUTPUT_FILE;
fi
unzip -o -j $OUTPUT_FILE WEB-INF/lib/*.jar -d $DIST_DIR;

# Step 4 Creating JAR
echo "Creating war file"
cd $DIST_DIR/../../
echo $PWD
jar -cvf javabridge-bundle.war 
cd -


