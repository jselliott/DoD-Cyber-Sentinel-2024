# LinkU (Medium)

A CTFd compatible docker image for a web challenge. Scenario:

I've been working on developing a new social network specifically for Cybersecurity Professionals. However, it keeps getting hacked (ironic, eh?). Can you find how the hackers seem to be able to access the admin area so easily?

## Setup

Run the included build-docker.sh script to build and deploy the container in docker.

## Solution

This medium level challenge will prompt the player to examine the provided source code to try to find the vulnerability that will allow them to escalate their priveleges. The site has a pretty small set of features. These include a registration and login page which is secured with a signed JWT token, a simple dashboard page, and a restricted admin page. The only other implemented feature is a simple button to switch the theme of the site between light and dark.

Clever players will notice that when the user logs in, the preferences JSON object in the database is loaded and then merged into the user object that is returned:

```python
# User claims
claims = {'username': user.username,
            'user_level': user.user_level,
            'exp': datetime.datetime.utcnow() + datetime.timedelta(minutes=30)}

# Load display preferences
preferences = json.loads(base64.b64decode(user.preferences))
claims.update(preferences)
```

This small detail calling the update() function means that dictionary keys from the preferences object will overwrite the ones in the claims dictionary if there is a conflict. This means that by modifying the payload from the theme setter to include a JWT claim such as "user_level", the player is able to store their desired level in the database. Then simply log out and log back in to get a new token which includes the overwritten value, giving them admin access.

Browsing to /admin, they are presented with the flag.