#!/bin/bash
docker rm -f dod_ctf_web_printer
docker build --tag=dod_ctf_web_printer .
docker run -p 1337:1337 --rm --name=dod_ctf_web_printer dod_ctf_web_printer