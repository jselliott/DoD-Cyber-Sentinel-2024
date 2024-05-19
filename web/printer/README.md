# Printer (Easy)

A CTFd compatible docker image for a web challenge. Scenario:

I have been trying to access the control panel for my multifunction printer but haven't had any luck remembering the password. Can you help me get logged in?

## Setup

Run the included build-docker.sh script to build and deploy the container in docker.

## Solution

Because this is an easy-level challenge, it focuses on one of the most basic techniques in web challenges, enumerating the challenge. If they look at robots.txt, then they will see there is an entry to disallow access to notes.txt.

Inside this file there is a randomly generated dev password which can be used to log in and then get the flag.