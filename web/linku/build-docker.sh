#!/bin/bash
docker rm -f dod_ctf_web_linku
docker build --tag=dod_ctf_web_linku .
docker run -p 1337:1337 --rm --name=dod_ctf_web_linku dod_ctf_web_linku