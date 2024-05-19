from flask import current_app as app
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import generate_password_hash, check_password_hash
import datetime
import json
import base64

db = SQLAlchemy()

class User(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(50), unique=True, nullable=False)
    password = db.Column(db.String(80), nullable=False)
    user_level = db.Column(db.String(10), nullable=False)
    preferences = db.Column(db.String(1024),nullable=False)


def login_user_db(username, password):

    user = User.query.filter_by(username=username).first()

    if not user:
        return False
    
    if check_password_hash(user.password, password):

        # User claims
        claims = {'username': user.username,
                  'user_level': user.user_level,
                  'exp': datetime.datetime.utcnow() + datetime.timedelta(minutes=30)}
        
        # Load display preferences
        preferences = json.loads(base64.b64decode(user.preferences))
        claims.update(preferences)
        
        return claims
    
    return False

def register_user_db(username, password):

    # Check if username exists
    check_user = User.query.filter_by(username=username).first()

    # If not
    if not check_user:

        # default preferences
        default_prefs = base64.b64encode(json.dumps({"theme":"light"}).encode())

        # Add new user to DB
        hashed_password = generate_password_hash(password)
        new_user = User(username=username, password=hashed_password, user_level="user", preferences=default_prefs)
        db.session.add(new_user)
        db.session.commit()
        
        return True

    return False

def update_preferences_db(username,pref_data):
    
    # Update current authenticated user
    user = User.query.filter_by(username=username).first()

    # If user exists
    if user:

        # Save preferences as base-64 JSON
        user.preferences = base64.b64encode(json.dumps(pref_data).encode())
        db.session.commit()

        return True
    
    return False