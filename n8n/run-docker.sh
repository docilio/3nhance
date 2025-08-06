#!/bin/bash

# Change to the directory where your docker-compose.yml file is located
cd ~/n8n || exit

# Run the commands
/usr/local/bin/docker-compose pull n8n            # Latest version of n8n
/usr/local/bin/docker-compose up -d --build


# This can also be used to update the docker
