import requests
import pyotp
import random

totp = pyotp.TOTP("JBSWY3DPEHPK3PXP")
seed = totp.now()
random.seed(seed)
num = random.randint(0,1000000)

url = "https://uscybercombine-countdown.chals.io/api/guess"
#url = "http://localhost:1337/api/guess"

R = requests.post(url,json={"guess":num})
print(R.text)