FROM mcr.microsoft.com/devcontainers/php:0-8.2

# Install Node.js 18 and build tools
RUN apt-get update \
  && apt-get install -y curl ca-certificates gnupg lsb-release build-essential git \
  && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
  && apt-get install -y nodejs \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /workspace

CMD ["sleep", "infinity"]
