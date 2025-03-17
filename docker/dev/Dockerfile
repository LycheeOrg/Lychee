FROM lychee-base:latest AS base

# Install development dependencies

RUN \
	apt-get install -y git composer nodejs

RUN \
    rm -rf /var/lib/apt/lists/* \
	&& apt-get clean -qy
