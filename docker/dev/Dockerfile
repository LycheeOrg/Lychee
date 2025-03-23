FROM lychee-base:latest AS base

# Install development dependencies

RUN \
    curl -fsSL https://deb.nodesource.com/setup_23.x -o nodesource_setup.sh \
    && bash nodesource_setup.sh \
    && apt-get install -y git composer nodejs

RUN \
    rm -rf /var/lib/apt/lists/* \
	&& apt-get clean -qy