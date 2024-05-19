#!/bin/bash
docker rm -f c1_web_party
docker build --tag=c1_web_party .
docker run -p 1337:80 --rm --name=c1_web_party c1_web_party