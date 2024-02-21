# MB net7 Postgres with Postgis extension

#   Postgres version details
ARG PSQL_VERSION=13

#   New database details
# ARG NEW_DB_NAME=imagoloci
# ARG NEW_DB_USER=imagoloci
# ARG NEW_DB_USER_PASSWORD=imagoloci

#   Select image from docker hub
FROM postgres:${PSQL_VERSION}

#   Do everything as root

ENV USER=sysadmin
ENV UID=1000
ENV GID=staff

USER root
ADD bashrc /root/.bashrc

RUN adduser \
    --disabled-password \
    --gecos "" \
    --home "$(pwd)" \
    --ingroup "$GID" \
    --no-create-home \
    --uid "$UID" \
    "$USER"

#   Install postgis and vim, clear aptitude cache and set initial script as executable
RUN build_packages="vim postgis postgresql-13-postgis-3" \
    && apt-get update && apt-get install -y $build_packages \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

USER "$USER"

