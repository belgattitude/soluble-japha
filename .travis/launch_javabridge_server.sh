#!/usr/bin/env bash

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_DIR="${SCRIPT_DIR}/.."

# soluble PHPJavabridge version
PJB_VERSION="7.1.3"
PJB_DIR="${SCRIPT_DIR}/downloads/php-java-bridge-${PJB_VERSION}"

# Webapp runner properties
WEBAPP_RUNNER_VERSION="8.5.38.0";
WEBAPP_RUNNER_URL="http://central.maven.org/maven2/com/github/jsimone/webapp-runner/${WEBAPP_RUNNER_VERSION}/webapp-runner-${WEBAPP_RUNNER_VERSION}.jar"
WEBAPP_RUNNER_JAR="${SCRIPT_DIR}/downloads/webapp-runner.jar"
WEBAPP_RUNNER_PORT=8083
WEBAPP_RUNNER_LOGFILE="${SCRIPT_DIR}/webapp-runner.${WEBAPP_RUNNER_PORT}.log"
WEBAPP_RUNNER_PIDFILE="${SCRIPT_DIR}/webapp-runner.${WEBAPP_RUNNER_PORT}.pid"
WEBAPP_RUNNER_ENABLE_BASIC_AUTH=true
WEBAPP_RUNNER_TOMCAT_USERS_LOCATION="${SCRIPT_DIR}/tomcat-users.xml"

JAVA_BIN=`which java`

cd $SCRIPT_DIR;

buildJavaBridgeServer() {

    echo "[*] Download and build PHPJavaBridge";
    cd downloads;
    wget https://github.com/belgattitude/php-java-bridge/archive/${PJB_VERSION}.zip -O pjb.zip;
    if [ -d "${PJB_DIR}" ]; then
        rm -rf "${PJB_DIR}";
    fi;
    unzip pjb.zip
    cd php-java-bridge-${PJB_VERSION};
    cp ${SCRIPT_DIR}/web.xml ./src/main/webapp/WEB-INF/web.xml
    ./gradlew war -I $SCRIPT_DIR/init.travis.gradle

    # restore path
    cd $SCRIPT_DIR;
}

downloadWebappRunner() {

    echo "[*] Download WebappRunner";
    if [ ! -f $WEBAPP_RUNNER_JAR ]; then
        wget $WEBAPP_RUNNER_URL -O $WEBAPP_RUNNER_JAR
    fi;
}

runJavaBridgeServerInBackground() {

    echo "[*] Starting JavaBridge server with webapp-runner (in background)";

    CMD="${JAVA_BIN} -jar ${WEBAPP_RUNNER_JAR} ${PJB_DIR}/build/libs/JavaBridgeTemplate.war --port ${WEBAPP_RUNNER_PORT}";

    if [ "${WEBAPP_RUNNER_ENABLE_BASIC_AUTH}" == "true" ]; then
       CMD="${CMD} --tomcat-users-location ${WEBAPP_RUNNER_TOMCAT_USERS_LOCATION}";
    fi;

    # Starting in background
    eval "${CMD} >${WEBAPP_RUNNER_LOGFILE} 2>&1 &disown; echo \$! > ${WEBAPP_RUNNER_PIDFILE}"

    SERVER_PID=`cat ${WEBAPP_RUNNER_PIDFILE}`;

    echo "[*] Server starter with PID $SERVER_PID";
    echo "    and command: $CMD";
}


# Here's the steps
buildJavaBridgeServer;
downloadWebappRunner;
runJavaBridgeServerInBackground;

# Stop server when developping
#kill `cat $WEBAPP_PIDFILE`


