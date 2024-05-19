#!/bin/bash
docker rm -f c1_web_chatter
docker build --tag=c1_web_chatter .
docker run -p 1337:80 -e NODE_ENV=development --rm --name=c1_web_chatter c1_web_chatter